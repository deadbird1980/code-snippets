package com.re.cms3.util
{
	import flash.events.Event;
	import flash.events.IOErrorEvent;
	import flash.net.URLLoader;
	import flash.net.URLRequest;
	import mx.controls.Alert;
	
	public class Config {
		//config item, coming from config file
		public static var BaseURL:String = '';
		public static var isUrlMode:Boolean = true;
		
		public static var phpSessionID:String;
		
		private static var callback:Function = null;
		
		public static var buildNumber:Number = CONFIG::buildNumber;
	
		//load config from xml
		public static function LoadConfig(callback:Function=null):void {
			var loader:URLLoader = new URLLoader();
			var request:URLRequest = new URLRequest("config.xml");
				
			loader.load(request);
			loader.addEventListener(Event.COMPLETE, onComplete);
			loader.addEventListener(IOErrorEvent.IO_ERROR, onIOError);
				
			Config.callback = callback;
		}

		//callback function for xml load
		private static function onComplete(event:Event):void {
			var loader:URLLoader = URLLoader(event.target);
			var externalXML:XML = new XML(loader.data);
	
			//initial static const
			BaseURL = externalXML.baseurl.@value;
			if (BaseURL.charAt(BaseURL.length-1) != '/') {
				BaseURL += '/';
			}
				
			isUrlMode = externalXML.isUrlMode.@value=='true' ? true:false;
								
			if (Config.callback != null) {
				Config.callback();
			}
		}
		
		private static function onIOError(evt:IOErrorEvent):void {
			Alert.show("fatal error. cannot load config from xml file\n\n" + evt.toString());
		}
	}
}
