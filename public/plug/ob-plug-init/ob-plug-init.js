var loadScriptCallBack = [];
var loadScriptProgress = false;
var loadScriptPlugs = {
	ichek: {
		source: 'plug/icheck-1-0-2/js/icheck.min.js',
		init: function() {return $().iCheck;}
	},
	textcounter: {
		source: 'plug/jquery-text-counter-master-0-8-0/js/textcounter.min.js',
		init: function() {return $().textcounter;}
	},
	fancybox: {
		source: 'public/plug/jquery-fancybox-2-1-7/js/jquery.fancybox.min2.js',
		css: 'public/plug/jquery-fancybox-2-1-7/css/jquery.fancybox.css',
		init: function() {return $().fancybox;}
	}
};

function loadScript(url, callback){
	var source = loadScriptPlugs[url]['source'];
	var scripts = loadScriptPlugs[url]['scripts'];
	
	if(loadScriptCallBack[source] == undefined){
		loadScriptCallBack[source] = [];
		loadScriptCallBack[source].push(callback);
	}else{
		if(loadScriptProgress){
			loadScriptCallBack[source].push(callback);
		}else{
			callback();
		}
		
		return false;
	}
	//console.log(callback);
	var scriptsTotal = Object.keys(scripts).length - 1;
	$.each(scripts, function(index, element){
		// console.log(index);
		var thisIndex = index;
		loadScriptProgress = true;
		var script = document.createElement("script")
		script.type = "text/javascript";
		script.src = element;
		document.getElementsByTagName("body")[0].appendChild(script);

		script.onload = function(){
			// console.log(index);
			// console.log(thisIndex);
			if(scriptsTotal == thisIndex){
				// console.log(loadScriptCallBack);
				$.each(loadScriptCallBack[source], function(index, element){
					element();
					delete loadScriptCallBack[source][index];
				});
				loadScriptProgress = false;
			}
		};
	});

	// console.log('css');
	var css = loadScriptPlugs[url]['css'];
	if(css != undefined){
		var cssDom = document.createElement("link")
		cssDom.rel = "stylesheet";
		cssDom.type = "text/css";
		cssDom.href = css;
		cssDom.media = 'all';
		
		document.getElementsByTagName("head")[0].appendChild(cssDom);
	}

	return false;
	loadScriptProgress = true;
	var script = document.createElement("script")
	script.type = "text/javascript";
	
	//console.log(script.readyState);
	if (script.readyState){  //IE
		script.onreadystatechange = function(){
			if (
				script.readyState == "loaded" ||
				script.readyState == "complete"
			){
				script.onreadystatechange = null;
				
				$.each(loadScriptCallBack[source], function(index, element){
					console.log(script.readyState);
					loadScriptCallBack[source.element];
					delete loadScriptCallBack[source][index];
				});
				
				loadScriptProgress = false;
				//callback();
			}
		};
	} else {  //Others
		//console.log(3);
		script.onload = function(){
			// console.log(loadScriptCallBack);
			$.each(loadScriptCallBack[source], function(index, element){
				//console.log(element);
				element();
				delete loadScriptCallBack[source][index];
			});
			loadScriptProgress = false;
			//console.log(loadScriptCallBack);
			//callback();
		};
	}

	script.src = source;
	document.getElementsByTagName("body")[0].appendChild(script);

	var css = loadScriptPlugs[url]['css'];
	if(css != undefined){
		var cssDom = document.createElement("link")
		cssDom.rel = "stylesheet";
		cssDom.type = "text/css";
		cssDom.href = css;
		cssDom.media = 'all';
		
		document.getElementsByTagName("head")[0].appendChild(cssDom);
	}
}

function jsScriptVerify(selector, plug, callback){
	// console.log(1);
	if(!$(selector).length){
	// console.log(11);
		return false;
	}
	
	// console.log(2);
	if(!loadScriptPlugs[plug]['init']()){
	// console.log(22);
	// console.log(loadScriptPlugs[plug]);
		loadScript(plug, callback);
		return false;
	}
	
	// console.log(3);
	callback();
}