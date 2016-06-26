<!DOCTYPE html>
<?PHP 
$choiceParams = $_GET["list"];
$choices = explode(",", $choiceParams);
$intervalParam = $_GET["seconds"];
?>
<html>
<head>
    <meta charset="UTF-8">
    <title>Pop Clock</title>
    <link rel="stylesheet" href="css/style.css">
    <script type="text/javascript" src="js/paper-full.min.js"></script>
    <script type="text/javascript" src="js/webfontloader.js"></script>
    <script type="text/paperscript" canvas="canvas">

        var TextBig = function() {
            this.paperPointText = null;
            this.paperShapeRect = null;
        }

        var TextSmall = function() {
            this.name = "";
            this.paperPointText = null;
            this.paperShapeRect = null;
        }

        var names = <?PHP echo json_encode($choices); ?>;
        if(names[0] == "") {
            names[0] = "pop";
            names[1] = "clock";
        }
        for(var i=0; i<names.length; i++) {
            names[i] = names[i].toUpperCase();
        }
        var nameIndex = 0;

        var timeCount = 0;
        var timeFreq = '<?PHP echo $intervalParam ?>';
        if(!timeFreq) {
            timeFreq = 1;
        }
        var monthsEng = ["JAN", "FEB", "MAR", "APR", "MAY", "JUN", "JUL", "AUG", "SEP", "OCT", "NOV", "DEC"];

        var bResize = false;
        var screenW = paper.view.bounds.width;
        var screenH = paper.view.bounds.height;
        var bFontsLoaded = false;
        
        var rectBg = new Shape.Rectangle({
                point: [0, 0],
                size: [screenW, screenH],
                fillColor: 'black'
            });

        var textBig = new TextBig();
        textBig.paperPointText = new PointText(new Point(0, 0));
        textBig.paperPointText.fontFamily = 'HelveticaNeueLTStd-Hv';
        textBig.paperPointText.fontSize = 0;
        textBig.paperPointText.fillColor = 'white';
        textBig.paperShapeRect = new Shape.Rectangle({
                point: [0, 0],
                size: [screenW, screenH],
                strokeColor: 'red',
                strokeWidth: 3
            });
        textBig.paperShapeRect.visible = false;


        var rectMask = new Shape.Rectangle({
                point: [0, 0],
                size: [screenW, screenH],
                fillColor: 'black'
            });

        var textSml = [];
        var textSmlX = 10;
        var textSmlY = 20;
        var textSmlPad = 10;
        for(var i=0; i<names.length; i++) {
            textSml[i] = new TextSmall();
            textSml[i].name = names[i];
            textSml[i].paperPointText = new PointText(new Point(50, 50));
            textSml[i].paperPointText.justification = 'left';
            textSml[i].paperPointText.fillColor = 'white';
            textSml[i].paperPointText.content = textSml[i].name;
            textSml[i].paperPointText.point.x = textSmlX;
            textSml[i].paperPointText.point.y = textSmlY;
            textSmlY += textSml[i].paperPointText.bounds.height + textSmlPad;

            textSml[i].paperPointText.visible = false; // disabled for now.
        }

        var textDate = new PointText(new Point(0, 0));
        textDate.fontFamily = 'HelveticaNeueLTStd-Lt';
        textDate.fontSize = 26;
        textDate.fillColor = 'white';

        var textTime = new PointText(new Point(0, 0));
        textTime.fontFamily = 'HelveticaNeueLTStd-Md';
        textTime.fontSize = 50;
        textTime.fillColor = 'white';

        var timeHrs = 0;
        var timeMin = 0;
        var timeSec = 0;

        var dateDay = 0;
        var dateMonth = 0;
        var dateYear = 0;

        function onFrame(event) {
            
            var date = new Date();
            
            var timeTotalMs = date.getTime();
            var timeTotalSec = Math.floor(timeTotalMs / 1000.0);
            var timeCountNew = Math.floor(timeTotalSec / timeFreq);
            var bTimeCountChanged = timeCount != timeCountNew;
            timeCount = timeCountNew;
            
            var timeHrsNew = date.getHours();
            var timeMinNew = date.getMinutes();
            var timeSecNew = date.getSeconds();

            var dateDayNew = date.getUTCDate();
            var dateMonthNew = date.getUTCMonth();
            var dateYearNew = date.getUTCFullYear();

            var bTimeHrsChanged = timeHrs != timeHrsNew;
            var bTimeMinChanged = timeMin != timeMinNew;
            var bTimeSecChanged = timeSec != timeSecNew;

            var bDateDayChanged = dateDay != dateDayNew;
            var bDateMonthChanged = dateMonth != dateMonthNew;
            var bDateYearChanged = dateYear != dateYearNew;

            timeHrs = timeHrsNew;
            timeMin = timeMinNew;
            timeSec = timeSecNew;

            dateDay = dateDayNew;
            dateMonth = dateMonthNew;
            dateYear = dateYearNew;

            var bTimeChanged = bTimeHrsChanged || bTimeMinChanged || bTimeSecChanged;
            var bDateChanged = bDateDayChanged || bDateMonthChanged || bDateYearChanged;

            if(bResize) {

                rectBg.fitBounds(new paper.Rectangle(0, 0, screenW, screenH), true);
            }

            var bFontResize = bResize || bFontsLoaded;
            if(bFontResize) {

                var fontSize = 10;
                var fontSizePrev = fontSize;
                var fontSizeInc = 10;
                
                var bFontResizeBig = (names.length > 0);
                while(bFontResizeBig == true) {

                    textBig.paperPointText.fontSize = fontSize;

                    var bFontSizeTooBig = false;
                    for(var i=0; i<names.length; i++) {
                        textBig.paperPointText.content = names[i];
                        if(textBig.paperPointText.bounds.width > screenW) {
                            bFontSizeTooBig = true;
                            break;
                        }
                    }

                    if(bFontSizeTooBig == true) {
                        textBig.paperPointText.fontSize = fontSizePrev;
                        break;
                    }

                    fontSizePrev = fontSize;
                    fontSize += fontSizeInc;
                }
                textBig.paperPointText.content = names[nameIndex];

                //------
                textTime.content = "00:00:00";

                var fontTimeSpaceX = screenW * 0.4;
                var fontTimeSpaceY = (screenH - (textBig.paperPointText.bounds.y + textBig.paperPointText.bounds.height)) * 0.5;

                fontSize = 10;
                fontSizePrev = fontSize;
                fontSizeInc = 2;

                var bFontResizeClock = true;
                while(bFontResizeClock == true) {

                    textTime.fontSize = fontSize;

                    var bFontSizeTooBig = false;
                    bFontSizeTooBig = bFontSizeTooBig || (textTime.bounds.width > fontTimeSpaceX);
                    bFontSizeTooBig = bFontSizeTooBig || (textTime.bounds.height > fontTimeSpaceY);
                    if(bFontSizeTooBig == true) {
                        textTime.fontSize = fontSizePrev;
                        textDate.fontSize = fontSizePrev * 0.5;
                        break;
                    }

                    fontSizePrev = fontSize;
                    fontSize += fontSizeInc;
                }
            }

            if(bTimeCountChanged && (names.length > 0)) {
                nameIndex = timeCount % names.length;
                textBig.paperPointText.content = names[nameIndex];
            }

            if(bTimeChanged || bDateChanged || bFontResize || bTimeCountChanged) {

                var timeHrsStr = "0";
                var timeMinStr = "0";
                var timeSecStr = "0";
                
                var dateDayStr = "0";
                var dateMonthStr = "0";
                var dateYearStr = "0";

                var pad = 20;
                var tx = 0;
                var ty = 0;

                if(timeHrs < 10) {
                    timeHrsStr += timeHrs;
                } else {
                    timeHrsStr = timeHrs;
                }

                if(timeMin < 10) {
                    timeMinStr += timeMin;
                } else {
                    timeMinStr = timeMin;
                }

                if(timeSec < 10) {
                    timeSecStr += timeSec;
                } else {
                    timeSecStr = timeSec;
                }

                if(dateDay < 10) {
                    dateDayStr += dateDay;
                } else {
                    dateDayStr = dateDay;
                }

                dateMonthStr = monthsEng[dateMonth];
                dateYearStr = dateYear;

                textTime.content = timeHrsStr + ":" + timeMinStr + ":" + timeSecStr;

                tx = Math.floor(screenW - textTime.bounds.width - pad);
                ty = Math.floor(screenH - pad);

                textTime.point.x = tx;
                textTime.point.y = ty;

                textDate.content = dateDayStr + " " + dateMonthStr + ", " + dateYearStr;

                tx = Math.floor(screenW - textDate.bounds.width - pad);
                ty -= textDate.bounds.height * 1.6;

                textDate.point.x = tx;
                textDate.point.y = ty;

                textBig.paperPointText.point.x = Math.floor((screenW - textBig.paperPointText.bounds.width) * 0.5);
                textBig.paperPointText.point.y = Math.floor((screenH + textBig.paperPointText.bounds.height * 0.5) * 0.5);
                textBig.paperShapeRect.bounds = textBig.paperPointText.bounds;

                rectMask.bounds = new paper.Rectangle(0, textBig.paperPointText.bounds.y + textBig.paperPointText.bounds.height, screenW, screenH);
            }

            bResize = false;
        }

        //------------------------------------------------------------
        function resize() {
            
            bResize = true;
            screenW = paper.view.bounds.width;
            screenH = paper.view.bounds.height;
        }

        function onResize(event) {
            resize();
        }
        resize();

        //------------------------------------------------------------
        function onMouseDown(event) {
            //
        }

        //------------------------------------------------------------
        function initFonts() {
            var WebFontConfig = {
              custom: {
                  families: ['HelveticaNeueLTStd-Hv', 'HelveticaNeueLTStd-Lt', 'HelveticaNeueLTStd-Md'],
                  urls: ['./css/fonts.css']
              },
              active: function() {
                  bFontsLoaded = true;
              }
            };
            WebFont.load(WebFontConfig);
        }
        
        initFonts();

    </script>
</head>
<body>
<div id="info"><a href="https://github.com/julapy/web-popclock">Pop Clock</a></div>
<canvas id="canvas" resize keepalive="true" hidpi="off"></canvas>
</body>
</html>
