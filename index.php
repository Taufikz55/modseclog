<?php
    require_once "parser-php-version.php";
    class MOdsecLog {
        
        public $rawLines;
        public $formattedLines = array();
        
        function __construct($logFilePath) {
            date_default_timezone_set("Asia/Makassar");
            
            $this->rawLines = file($logFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            
            $this->formatLines();
        }
        
        function formatLines(){
            for($index = count($this->rawLines) - 1; $index >= 0; $index--){
                $line = $this->rawLines[$index];
                
                $this->formattedLines[$index] = array();
                
                $dateAndTime = substr($line, 1, 24);            
                $timestamp = strtotime($dateAndTime);            
                $now = time();            
                $timeDifference = $now - $timestamp;
                
                $line = substr($line, 27);
                $line = str_replace("[client 127.0.0.1]", "", $line);
                $matches = array();
                $match = preg_match('[\w+]', $line, $matches);
                
                if($match){
                    $level = $matches[0];
                } else {
                    $level = "unknown";
                }
                
                $line = str_replace("[" . $level . "] ", "", $line);
                
                
                if($timeDifference < 86400) {
                    $formattedDate = "[Today] " . strftime("%l:%M%p", $timestamp);
                    
                    $this->formattedLines[$index]["isToday"] = true;
                } else {
                    $formattedDate = strftime("%b %d %y %l:%M%p", $timestamp);
                    
                    $this->formattedLines[$index]["isToday"] = false;
                }
                
                $this->formattedLines[$index]["level"] = $level;
                $this->formattedLines[$index]["message"] = $line;
                $this->formattedLines[$index]["dateAndTime"] = $formattedDate;
            }
        }
        
    }
    
    $ModsecLog = new ModsecLog('/var/log/apache2/error.log');
?>
<!doctype html>
<html>
    <head>
        <title>Modsecurity Log</title>
        <meta charset="utf-8">
        <!--<meta http-equiv="refresh" content="5">-->
        <style>
            body {
                font:1em/1.2 normal Arial, sans-serif;
            }
            ol li {
                padding:6px;
                border-bottom:1px solid #ccc;
            }

            .crit {
                color: red;
            }

            .WARNING !!! {
                color: red;
            }

            .warn {
                color: yellow;
            }

            .today .message {
                background-color:#FDFFC3;
            }

            .time {
                font-size:0.8em;
            }
        </style>
    </head>
    <body>
        <ol id="log">
            <?php foreach($ModsecLog->formattedLines as $line): ?>
                <li <?php if($line["isToday"]): ?>class="today"<?php endif; ?>>
                    <span class="<?php echo $line["level"]; ?> level"><?php echo $line["level"]; ?></span>
                    <span class="message"><?php echo $line["message"]; ?></span>
                    <div class="time"><?php echo $line["dateAndTime"]; ?></div>
                </li>
            <?php endforeach; ?>
        </ol>
    </body>
</html>