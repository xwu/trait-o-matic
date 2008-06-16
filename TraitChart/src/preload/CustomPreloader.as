package preload
{
	import flash.display.Sprite;
	import flash.events.Event;
	import flash.events.ProgressEvent;
	
	import mx.events.FlexEvent;
	import mx.preloaders.DownloadProgressBar;
	
	// custom preloader based on the version at http://jaapkooiker.nl/
	public class CustomPreloader extends DownloadProgressBar
	{
		
		[Embed(source="spinner.swf", symbol="spinner")]
		private var FlashSymbol:Class;
		private var sprite:Sprite;
		private var clip:*;
		
		public function CustomPreloader()
		{
			super();
			this.clip = new FlashSymbol(); // embedded clips have their ActionScript stripped
			this.addChild(this.clip);
		}
		
		public override function set preloader(value:Sprite):void
		{	
			value.addEventListener(ProgressEvent.PROGRESS, onDownloadProgress);	   
			value.addEventListener(Event.COMPLETE, onDownloadComplete);
			value.addEventListener(FlexEvent.INIT_PROGRESS, onInitProgress);
			value.addEventListener(FlexEvent.INIT_COMPLETE, onInitComplete);
			stage.addEventListener(Event.RESIZE, onResize);
			
			sprite = value;
			onResize(); // center our preloader immediately
		}
 
		private function onResize(event:Event = null):void
		{	
			x = (stage.stageWidth / 2) - (clip.width / 2);
			y = (stage.stageHeight / 2) - (clip.height / 2); 
		}
  
		private function onDownloadProgress(event:ProgressEvent):void
		{
			// nada
		}
			 
		private function onDownloadComplete(event:Event):void
		{
			// nada
		}
			  
		private function onInitProgress(event:FlexEvent):void
		{
			// nada		
		}
			
		private function onInitComplete(event:FlexEvent):void
		{
			sprite.removeEventListener(ProgressEvent.PROGRESS, onDownloadProgress);	  
			sprite.removeEventListener(Event.COMPLETE, onDownloadComplete);
			sprite.removeEventListener(FlexEvent.INIT_PROGRESS, onInitProgress);
			sprite.removeEventListener(FlexEvent.INIT_COMPLETE, onInitComplete);
			stage.removeEventListener(Event.RESIZE, onResize);
			clip.stop();
			dispatchEvent(new Event(Event.COMPLETE));
		}
	}
}