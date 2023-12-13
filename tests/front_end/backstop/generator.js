const arguments = {};
const fs = require('fs');
// All console arguments to object type
for(let arg of process.argv.splice(2)){
    arg = arg.split('=');
    arguments[arg[0]] = arg[1].replace(/\[*\]*/gm,'').split(',');
}
const medias = [];
if(arguments.media &&  arguments.pages.length > 0){
    for (let key in arguments.media){
        let media = {},
            type = arguments.media[key].split(':'),
            resolution = type[1].split('x');
        media.label = type[0];
        media.width = parseInt(resolution[0]);
        media.height = parseInt(resolution[1]);
        medias.push(media);
    }
}
// Create scenario with array of pages
const scenarios = [];
if(arguments.pages && arguments.pages.length > 0){
    for(let key in arguments.pages){
        if(!fs.existsSync(`pages/${arguments.pages[key]}.js`)){
            continue;
        }
        for(let scenario of require(`./pages/${arguments.pages[key]}`)){
            scenarios.push(scenario)
        }
    }
}
const debugMode = arguments.debugMode[0] === "true";
const reportArray = [];
if (arguments?.report?.[0] === "true") {
    reportArray.push("browser");
}
const asyncCaptureLimit = Number(arguments.asyncCaptureLimit[0]);
const asyncCompareLimit = Number(arguments.asyncCompareLimit[0]);

// Backstop JSON
const backstopJSON = {
    "id": "Page",
    "viewports": medias,
    "onBeforeScript": "puppet/onBefore.js",
    "onReadyScript": "puppet/onReady.js",
    "scenarios": scenarios,
    "paths": {
        "bitmaps_reference": "assets/reference",
        "bitmaps_test": "assets/test",
        "engine_scripts": "app",
        "html_report": "report/html",
        "ci_report": "report/ci"
    },
    "report": reportArray,
    "engine": "puppeteer",
    "engineOptions": {
        "args": ["--no-sandbox"]
    },
    "asyncCaptureLimit": asyncCaptureLimit || 2,
    "asyncCompareLimit": asyncCompareLimit || 10,
    "debug": debugMode || false,
    "debugWindow": debugMode || false
}

// Create JSON
fs.writeFile('backstop.json', JSON.stringify(backstopJSON), function(err){
    if(err) throw err;
    console.log('Backstop file was created!')
})
