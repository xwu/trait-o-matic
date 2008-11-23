package trait
{
	import flash.display.Sprite;
	
	import mx.controls.VSlider;
	import mx.controls.sliderClasses.SliderLabel;

	public class CustomVSlider extends VSlider
	{
		private var _innerCircle:Sprite = new Sprite();
		private var _outerCircle:Sprite = new Sprite();
		private var _minValueLabel:SliderLabel = new SliderLabel();
		private var _maxValueLabel:SliderLabel = new SliderLabel();
		
		public function CustomVSlider()
		{
			super();
			
			this.addChild(_outerCircle);
			this.addChild(_innerCircle);
//			this.addChild(_maxValueLabel);
//			this.addChild(_minValueLabel);

			this.thumbCount = 2;
			this.setStyle("thumbOffset", 6);
			this.liveDragging = true;
		}
		
		public function get minValue():Number { return Number(_minValueLabel.text); }
		public function set minValue(n:Number):void { _minValueLabel.text = n.toString(); }
		
		public function get maxValue():Number { return Number(_maxValueLabel.text); }
		public function set maxValue(n:Number):void { _maxValueLabel.text = n.toString(); }
		
		override protected function updateDisplayList(unscaledWidth:Number, unscaledHeight:Number):void
		{
			// get values
			var v:Array = this.values;
			var innerRadius:Number = (v[0] as Number) / 2;
			var outerRadius:Number = (v[1] as Number) / 2;
			
			// draw circles
			_innerCircle.graphics.clear();
			_innerCircle.graphics.lineStyle(1, 0x888888);
			_innerCircle.graphics.beginFill(0xFFFFFF, 1);
			_innerCircle.graphics.drawCircle(this.width / 2, this.height - innerRadius - 6, innerRadius);
			_innerCircle.graphics.endFill();
			
			_outerCircle.graphics.clear();
			_outerCircle.graphics.lineStyle(1, 0x888888);
			_outerCircle.graphics.beginFill(0xFFFFFF, 1);
			_outerCircle.graphics.drawCircle(this.width / 2, this.height - outerRadius - 6, outerRadius);
			_outerCircle.graphics.endFill();

			// call superclass function
			super.updateDisplayList(unscaledWidth, unscaledHeight);
			
/*			_minValueLabel.x = this.width / 2 - 6;
			_minValueLabel.y = this.height - innerRadius * 2 - 6;
			_maxValueLabel.x = this.width / 2 - 6;
			_maxValueLabel.y = this.height - outerRadius * 2 - 6; */
		}
	}
}