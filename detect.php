<?php

	header("Content-Type: text/javascript");
	header("Cache-Control: no-cache, no-store, must-revalidate, max-age=0");
	header("Pragma: no-cache");
	header("Expires: 0");

	include_once('libraries/utilities.php');
	include_once('libraries/whichbrowser.php');
		
	$options = array('headers' => getallheaders());
	if (isset($_REQUEST['ua'])) $options['useragent'] = $_REQUEST['ua'];
	if (isset($_REQUEST['e'])) $options['engine'] = intval($_REQUEST['e']);
	if (isset($_REQUEST['f'])) $options['features'] = intval($_REQUEST['f']);
	if (isset($_REQUEST['w'])) $options['width'] = intval($_REQUEST['w']);
	if (isset($_REQUEST['h'])) $options['height'] = intval($_REQUEST['h']);
	$detected = new WhichBrowser($options);
	
?>
	
var WhichBrowser = (function(){
	
 	var WhichBrowser = function() { this.initialize.apply(this, arguments) };
	WhichBrowser.prototype = {
		initialize: function(options) {
			this.options = {
			}
			
<?php $detected->toJavaScript(); ?>
		},
		
		a: function(s) {
			return (/^[aeiou]/i.test(s) ? 'an ' : 'a ') + s;
		},
		
		isX: function() {
			var valid = true;
			var x = arguments[0];
			
			if (arguments.length >= 2) {
				valid = valid && this[x].name == arguments[1];
			}
			
			if (arguments.length >= 4 && this[x].version && valid) {
				valid = valid && this[x].version.is(arguments[2], arguments[3]);
			}

			return valid;
		},
		
		isBrowser: function() { var a = Array.prototype.slice.call(arguments); a.unshift('browser'); return this.isX.apply(this, a); },
		isEngine: function() { var a = Array.prototype.slice.call(arguments); a.unshift('engine'); return this.isX.apply(this, a); },
		isOs: function() { var a = Array.prototype.slice.call(arguments); a.unshift('os'); return this.isX.apply(this, a); },
				
		isDevice: function(d) {
			return this.device.model == d;
		},

		isType: function() {
			var valid = false;
			for (var a = 0; a < arguments.length; a++) valid = valid || arguments[a] == this.device.type;
			return valid;
		},

		toJSON: function() {
			return {
				browser:	this.browser.toJSON(),
				os:			this.os.toJSON(),
				engine:		this.engine.toJSON(),
				device:		this.device.toJSON()
			};
		},
		
		toString: function() {
			var prefix = this.camouflage ? 'an unknown browser that imitates ' : '';
			var browser = this.browser.toString();
			var os = this.os.toString();
			var engine = this.engine.toString();
			var device = this.device.toString();
			
			if (!device && !os && this.device.type == 'television') device = 'television';
			if (!device && this.device.type == 'emulator') device = 'emulator';
		
			if (browser && os && device) return prefix + browser + ' on ' + this.a(device) + ' running ' + os;
			if (browser && !os && device) return prefix + browser + ' on ' + this.a(device);
			if (browser && os && !device) return prefix + browser + ' on ' + os;
			if (!browser && os && device) return prefix + this.a(device) + ' running ' + os;
			if (browser && !os && !device) return prefix + browser;
			if (!browser && !os && device) return prefix + this.a(device);
			if (this.device.type == 'desktop' && os && engine != '' && !device) return 'an unknown browser based on ' + engine + ' running on ' + os;
			if (this.browser.stock && os && !device) return os;
			if (this.browser.stock && engine != '' && !device) return 'an unknown browser based on ' + engine;
			
			return 'an unknown browser';
		}
	};

	var Browser = function() { this.initialize.apply(this, Array.prototype.slice.call(arguments)) };
	Browser.prototype = {
		initialize: function(v) {
			this.name = v.name || null;
			this.version = v.version || null;
			
			this.stock = v.stock || false;
			this.channel = v.channel || null;
			this.mode = v.mode || null;
			this.hidden = v.hidden || false;
		},
		
		toJSON: function() {
			return {
				name:		this.name,
				version:	this.version.toJSON(),
				stock:		this.stock,
				channel:	this.channel,
				mode:		this.mode,
				hidden:		this.hidden
			}
		},
		
		toString: function() {
			return (this.name ? this.name + (this.channel ? ' ' + this.channel : '') + (this.version ? ' ' + this.version.toString() : '') : '');
		}
	}

	var Engine = function() { this.initialize.apply(this, Array.prototype.slice.call(arguments)) };
	Engine.prototype = {
		initialize: function(v) {
			this.name = v.name || null;
			this.version = v.version || null;
		},
		
		toJSON: function() {
			return {
				name:		this.name,
				version:	this.version.toJSON()
			}
		},
		
		toString: function() {
			return (this.name ? this.name : '');
		}
	}

	var Os = function() { this.initialize.apply(this, Array.prototype.slice.call(arguments)) };
	Os.prototype = {
		initialize: function(v) {
			this.name = v.name || null;
			this.version = v.version || null;
		},
		
		toJSON: function() {
			return {
				name:		this.name,
				version:	this.version.toJSON()
			}
		},
		
		toString: function() {
			return (this.name ? this.name + (this.version ? ' ' + this.version.toString() : '') : '');
		}
	}

	var Device = function() { this.initialize.apply(this, Array.prototype.slice.call(arguments)) };
	Device.prototype = {
		initialize: function(v) {
			this.type = v.type || null;
			this.identified = v.identified || false;
			this.manufacturer = v.manufacturer || null;
			this.model = v.model || null;
			this.series = v.series || null;
		},
		
		toJSON: function() {
			return {
				type:			this.type,
				identified:		this.identified,
				manufacturer:	this.manufacturer,
				model:			this.model,
				series:			this.series
			};
		},
		
		toString: function() {
			if (this.identified)			
				return ((this.manufacturer || '') + ' ' + (this.model || '') + ' ' + (this.series || '')).replace(/^\s+|\s+$/g,'');
			else
				return (this.model ? 'unrecognized device (' + this.model + ')' : '');
		}
	}

 	var Version = function() { this.initialize.apply(this, Array.prototype.slice.call(arguments)) };
	Version.prototype = {
		initialize: function(v) {
			this.original = v.value || null;
			this.alias = v.alias || null;
			this.details = v.details || null;
			this.builds = typeof v.builds != 'undefined' ? v.builds : true;

			this.major = 0;
			this.minor = null;
			this.revision = null;
			this.build = null;
			this.type = '';
						
			var match;
			if (match = /([0-9]+)(?:\.([0-9]+))?(?:\.([0-9]+))?(?:\.([0-9]+))?(?:([ab])([0-9]+))?/.exec(this.original)) {
				if (typeof match[1] != 'undefined') {
					this.major = match[1];
				}

				if (typeof match[2] != 'undefined') {
					this.minor = match[2];
				}

				if (typeof match[3] != 'undefined') {
					this.revision = match[3];
				}

				if (typeof match[4] != 'undefined') {
					this.build = match[4];
				}

				if (typeof match[5] != 'undefined') {
					switch(match[5]) {
						case 'a':	this.type = 'alpha'; break;
						case 'b':	this.type = 'beta'; break;
					}

					if (typeof match[6] != 'undefined') {
						this.build = match[6];
					}
				}
			}
		},
		
		is: function(v) {
			var valid = false;
			
			if (arguments.length > 0) {
				var operator = '=';
				var compare = null;
				
				if (arguments.length == 1) {
					compare = new Version({ value: arguments[0] });
				}
				
				if (arguments.length >= 2) {
					operator = arguments[0];
					compare = new Version({ value: arguments[1] });
				}
				
				if (compare) {
					var v1 = v2 = '';
					
					if (compare.major && this.major) {
						v1 += this.major;
						v2 += compare.major;
						
						if (compare.minor && this.minor) {
							v1 += '.' + ('0000' + this.minor).slice(-4);
							v2 += '.' + ('0000' + compare.minor).slice(-4);
						
							if (compare.revision && this.revision) {
								v1 += ('0000' + this.revision).slice(-4);
								v2 += ('0000' + compare.revision).slice(-4);
							}
						}
					}
				
					v1 = parseFloat(v1);
					v2 = parseFloat(v2);
				
					switch (operator) {
						case '<':	valid = v1 < v2; break;
						case '<=':	valid = v1 <= v2; break;
						case '=':	valid = v1 == v2; break;
						case '>':	valid = v1 > v2; break;
						case '>=':	valid = v1 >= v2; break;
					}
				}
				
				return valid;
			}
			
			return false;
		},
		
		valueOf: function() {
			return parseFloat('' + this.major + '.' + ('0000' + this.minor).slice(-4) + ('0000' + this.revision).slice(-4));
		},
		
		toJSON: function() {
			var o = {
				value:		this.toString(),
				details:	this.details,
				original:	this.original,
				major:		this.major,
				minor:		this.minor,
				build:		this.build,
				revision:	this.revision
			};
			
			if (this.type) o.type = this.type;
			if (this.alias) o.alias = this.alias;
			
			return o;
		},
		
		toString: function() {
			if (this.alias)
				return this.alias;

			var version = '';

			if (this.major || this.minor) {
				var v = [];
				v.push(this.major);
				if (this.minor != '' && this.minor != null) v.push(this.minor);
				if (this.revision != '' && this.revision != null) v.push(this.revision);
				if (this.type == '' && this.build) v.push(this.build);
				if (this.details < 0) v.splice(this.details, 0 - this.details);
				if (this.details > 0) v.splice(this.details, v.length - this.details);
				
				if (!this.builds) {
					for (var i = 0; i < v.length; i++) {
						if (v[i] > 999) {
							v.splice(i, 1);
							i--;
						}					
					}
				}
				
				version = v.join('.');
			
				if (this.type != '') version += this.type[0] + (this.build ? this.build : '');
			}

			return version;
		}
	};

	return WhichBrowser;
})();	

var checkPlugin = function(browser) {
    if (browser.name == "Internet Explorer") {
        try {
            var control = new ActiveXObject("webrtceverywhere.WebRTC");
            return true;
        } catch (e) {
            return false;
        }
    } else if (browser.name == "Safari") {
        var b = false;
        for (var i in navigator.plugins) {
            var plugin = navigator.plugins[i];
            if (plugin.filename && plugin.filename == "webrtc-everywhere.plugin") {
                b = true;
                break;
            }
        }
        return b;
    } else {
        alert("this function should be call by IE or Safari");
    }
    return true;
}

var checkBrowser = function (browser, onSuccess, onError) {
    if (browser.name == "Chrome") {
        if (browser.version.major >= 26 && navigator.webkitGetUserMedia) {
            onSuccess();
        } else {
            onError(2, "您的浏览器版本过低，请更新最新版本");
        }
    } else if (browser.name == "Firefox" && navigator.mozGetUserMedia) {
        if (browser.version.major > 33) {
            onSuccess();
        } else {
            onError(2, "您的浏览器版本过低，请更新最新版本");
        }
    } else if (browser.name == "Internet Explorer") {
        if (browser.version.major > 10) {
            if (checkPlugin(browser)) {
                onSuccess();
            } else {
                onError(3, "您的浏览器需要安装插件才能视频通话");
            }
        } else {
            onError(2, "您的浏览器版本过低，请更新最新版本");
        }
    } else if (browser.name == "Safari") {
        if (browser.version.major > 6) {
            if (checkPlugin(browser)) {
                onSuccess();
            } else {
                onError(3, "您的浏览器需要安装插件才能视频通话");
            }
        } else {
            onError(2, "您的浏览器版本过低，请更新最新版本");
        }
    } else {
        onError(1, "您的浏览器不支持视频通话，请安装Chrome浏览器");
    }
}

var getPlugin = function () {
    return document.getElementById("WebrtcEverywherePluginId");
}

var installPlugin = function (browser, onSuccess, onError) {
    if (document.getElementById("WebrtcEverywherePluginId")) {
        return;
    }

    var pluginObj = document.createElement('object');
    if (browser.name == "Internet Explorer") {
        pluginObj.setAttribute('classid', 'CLSID:7FD49E23-C8D7-4C4F-93A1-F7EACFA1EC53');
        pluginObj.setAttribute('codebase', 'setup.exe#version=1.0.0.1');
    } else if (browser.name == "Safari") {
        pluginObj.setAttribute('type', 'application/webrtc-everywhere');
    } else {
        onError("not suitable plugins");
        return;
    }
    pluginObj.setAttribute('id', 'WebrtcEverywherePluginId');
    pluginObj.setAttribute('width', '0');
    pluginObj.setAttribute('height', '0');
    document.body.appendChild(pluginObj);
}

var checkDevice = function(browser, onSuccess, onError) {
    var getUserMedia = null;
    if (browser.name == "Firefox") {
        getUserMedia = navigator.mozGetUserMedia.bind(navigator);
    } else if (browser.name == "Chrome") {
        getUserMedia = navigator.webkitGetUserMedia.bind(navigator);
    } else if (browser.name == "Internet Explorer") {
        installPlugin(browser);
        getUserMedia = navigator.getUserMedia = function (constraints, successCallback, errorCallback) {
           var plugin = getPlugin();
           if (plugin) {
               plugin.getUserMedia(constraints, successCallback, errorCallback);
               console.log("Attaching media stream");
           } else {
               onError();
           }
        }
    }

    var onMediaSuccess = function (stream) {
        stream.stop();
        onSuccess();
    };
    if (getUserMedia) {
        getUserMedia({video: true, audio: true}, onMediaSuccess, onError);
    } else {
        onError("browser is not webrtc capable");
    }
}

