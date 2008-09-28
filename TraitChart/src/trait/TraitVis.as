package trait
{
	import flare.display.TextSprite;
	import flare.flex.FlareVis;
	import flare.scale.ScaleType;
	import flare.util.palette.ColorPalette;
	import flare.util.palette.SizePalette;
	import flare.vis.controls.HoverControl;
	import flare.vis.controls.SelectionControl;
	import flare.vis.data.Data;
	import flare.vis.data.DataSprite;
	import flare.vis.events.SelectionEvent;
	import flare.vis.operator.encoder.ColorEncoder;
	import flare.vis.operator.encoder.PropertyEncoder;
	import flare.vis.operator.encoder.SizeEncoder;
	import flare.vis.operator.layout.AxisLayout;
	
	import flash.events.Event;
	import flash.filters.DropShadowFilter;
	import flash.filters.GlowFilter;
	import flash.geom.Point;
	import flash.geom.Rectangle;
	
	import mx.controls.ToolTip;
	import mx.events.FlexEvent;
	import mx.events.ResizeEvent;
	import mx.managers.ToolTipManager;
	
	public class TraitVis extends FlareVis
	{
		
		private var _xAxisWidth:uint = 48;
		private var _yAxisHeight:uint = 48;
		private var _propertyEncoderPosition:uint = 0;
		private var _axisLayoutPosition:uint = 1;
		private var _sizeEncoderPosition:uint = 2;
		private var _colorEncoderPosition:uint = 3;
			
		public static function getPlaceholderData(n:int):Data
		{
			var data:Data = new Data();
			var d:DataSprite;
			var i:uint = 0;
			
			for (; i<10 && i<n; ++i)
			{
				d = data.addNode({
					x: int(1 + 9 * Math.random()),
					y: int(200 * (Math.random() - 0.5)),
					size: int(200 * (Math.random() - 0.5)),
					color: int(1 + 9 * Math.random()),
					label: "Placeholder data point"
				});
			}
			for (; i<n; ++i)
			{
				d = data.addNode({
					x: int(1 + 99 * Math.random()),
					y: int(200 * (Math.random() - 0.5)),
					size: int(200 * (Math.random() - 0.5)),
					color: int(1 + 9 * Math.random()),
					label: "Placeholder data point"
				});
			}
			return data;
		}
		
		public function TraitVis(data:Data = null)
		{
			// call the superclass
			super(data == null ? getPlaceholderData(0) : data);
			
			// do some housekeeping...
			ToolTipManager.showDelay = 0;
			this.addEventListener(FlexEvent.UPDATE_COMPLETE, addResizeListenerAndResize);
			
			// set default display properties
			var xField:String = "data.x";
			var yField:String = "data.y";
			var sizeField:String = "data.size";
			var colorField:String = "data.color";
			this.setDisplayProperties(xField, yField, sizeField, colorField);

			// other properties
//			this.visualization.xyAxes.xAxis.fixLabelOverlap = false; // keep overlapping labels
			this.visualization.xyAxes.xAxis.labelTextMode = TextSprite.DEVICE;
			this.visualization.xyAxes.yAxis.labelTextMode = TextSprite.DEVICE;
			this.visualization.update();
			this.visualization.continuousUpdates = true;
			
			// add mouse over
			var hc:HoverControl = new HoverControl(DataSprite, HoverControl.MOVE_AND_RETURN,
				function(e:SelectionEvent):void
				{
					var d:DataSprite = e.item
					if (!d.visible)
						return;
					var topLeft:Point = d.localToGlobal(new Point(0, 0));
					d.filters = [new GlowFilter(0xffffff, 1, 6, 6, 10), new DropShadowFilter(0, 90, 0x884cb825, 1, 4, 4, 2)];
					if (!d.data.toolTip)
						d.data.toolTip = ToolTipManager.createToolTip(d.data.label, topLeft.x - d.width / 2, topLeft.y + d.height / 2);
				},
				function(e:SelectionEvent):void
				{
					var d:DataSprite = e.item
					if (!d.data.isSelected)
						d.filters = null;
					if (d.data.toolTip)
					{
						ToolTipManager.destroyToolTip(d.data.toolTip);
						delete d.data.toolTip;
					}
				}
			);
			this.visualization.controls.add(hc);

			// add selection control
			var sc:SelectionControl = new SelectionControl(DataSprite,
				function(e:SelectionEvent):void
				{
					for each (var d:DataSprite in e.items)
					{
						if (!d.visible)
							return;
						var topLeft:Point = d.localToGlobal(new Point(0, 0));
						d.data.isSelected = true;
						d.filters = [new GlowFilter(0xffffff, 1, 6, 6, 10), new DropShadowFilter(0, 90, 0x884cb825, 1, 4, 4, 2)];
					}
				},
				function(e:SelectionEvent):void
				{
					for each (var d:DataSprite in e.items)
					{
						d.data.isSelected = false;
						d.filters = null;
					}
				},
			this.visualization);
			sc.fillColor = 0x999999;
			sc.fillAlpha = 0.3;
			sc.lineWidth = 1;
			sc.lineColor = 0x999999;
			sc.lineAlpha = 0.4;
			this.visualization.controls.add(sc);
		}

		public function setDisplayProperties(xField:String, yField:String, sizeField:String, colorField:String):void {
			// set line and fill properties
			//TODO: turn these into styles
			var pe:PropertyEncoder = new PropertyEncoder();
			pe.values = {lineColor: 0xFF666666, lineWidth: 1};
//			pe.values = {lineColor: 0xcc4cb825, lineWidth: 2};
			this.visualization.operators.setOperatorAt(_propertyEncoderPosition, pe);

			// set axis properties
			var al:AxisLayout = new AxisLayout(xField, yField);
//			al.initAxes = AxisLayout.ALWAYS;
			this.visualization.operators.setOperatorAt(_axisLayoutPosition, al);

			// set size properties
			var se:SizeEncoder = new SizeEncoder(sizeField, Data.NODES, new SizePalette(1, 24));
			se.scale.scaleType = ScaleType.LINEAR;
			this.visualization.operators.setOperatorAt(_sizeEncoderPosition, se);

			// set color properties
			var ce:ColorEncoder = new ColorEncoder(colorField, Data.NODES, "fillColor", ScaleType.LINEAR);
			this.visualization.operators.setOperatorAt(_colorEncoderPosition, ce);

//			this.visualization.operators.add(new ShapeEncoder(field1));	
		}
		
		public function get xAxisWidth():uint { return _xAxisWidth; }
		public function set xAxisWidth(x:uint):void { _xAxisWidth = x; resizeVisualization(); }

		public function get yAxisHeight():uint { return _yAxisHeight; }
		public function set yAxisHeight(y:uint):void { _yAxisHeight = y; resizeVisualization(); }
		
		public function get propertyEncoder():PropertyEncoder { return (this.visualization.operators.getOperatorAt(_propertyEncoderPosition) as PropertyEncoder); }
		public function set propertyEndoer(pe:PropertyEncoder):void { this.visualization.operators.setOperatorAt(_propertyEncoderPosition, pe); }
		
		public function get axisLayout():AxisLayout { return (this.visualization.operators.getOperatorAt(_axisLayoutPosition) as AxisLayout); }
		public function set axisLayout(al:AxisLayout):void { this.visualization.operators.setOperatorAt(_axisLayoutPosition, al); }
		
		public function get sizeEncoder():SizeEncoder { return (this.visualization.operators.getOperatorAt(_sizeEncoderPosition) as SizeEncoder); }
		public function set sizeEncoder(se:SizeEncoder):void { this.visualization.operators.setOperatorAt(_sizeEncoderPosition, se); }
		
		public function get colorEncoder():ColorEncoder { return (this.visualization.operators.getOperatorAt(_colorEncoderPosition) as ColorEncoder); }
		public function set colorEncoder(ce:ColorEncoder):void { this.visualization.operators.setOperatorAt(_colorEncoderPosition, ce); }
		
		private function addResizeListenerAndResize(evt:Event):void
		{
			callLater(resizeVisualization);
			this.addEventListener(ResizeEvent.RESIZE, resizeVisualization);
		}
		
		private function resizeVisualization(evt:Event = null):void
		{
			this.visualization.x = _xAxisWidth;
//			this.visualization.y = 0;
			this.visualization.y = _yAxisHeight;

			if (this.visHeight != this.height - _yAxisHeight * 2)
				this.visHeight = this.height - _yAxisHeight * 2;
			if (this.visWidth != this.width - _xAxisWidth * 2)
				this.visWidth = this.width - _xAxisWidth * 2;

			this.scrollRect = new Rectangle(0, 0, this.width, this.height);			
		}
	}
}