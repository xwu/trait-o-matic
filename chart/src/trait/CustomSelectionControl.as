package trait
{
	import flare.vis.events.SelectionEvent;
	
	import flash.display.DisplayObject;
	import flash.display.DisplayObjectContainer;
	import flash.display.Graphics;
	import flash.display.InteractiveObject;
	import flash.display.Shape;
	import flash.display.Stage;
	import flash.events.Event;
	import flash.events.MouseEvent;
	import flash.geom.Rectangle;
	import flash.utils.Dictionary;
	
	[Event(name="select", type="flare.vis.events.SelectionEvent")]
	[Event(name="deselect", type="flare.vis.events.SelectionEvent")]
		
	public class CustomSelectionControl extends Control
	{
		private var _r:Rectangle = new Rectangle();
		private var _drag:Boolean = false;
		private var _shape:Shape = new Shape();
		private var _hit:InteractiveObject;
		private var _stage:Stage;
		private var _sel:Dictionary = new Dictionary;
		
		private var _add0:DisplayObject = null;
		private var _rem0:DisplayObject = null;
		private var _add:Array = null;
		private var _rem:Array = null;
				
		/** The active hit area over which pan/zoom interactions can be performed. */
		public function get hitArea():InteractiveObject { return _hit; }
		public function set hitArea(hitArea:InteractiveObject):void {
			if (_hit != null) onRemove();
			_hit = hitArea;
			if (_object.stage != null) onAdd();
		}
		
		/**
		 * Creates a new CustomSelectionControl.
		 * @param container the container object to monitor for selections
		 * @param filter an optional Boolean-valued filter determining which
		 *  items are eligible for selection.
		 */
		public function CustomSelectionControl(container:InteractiveObject=null,
					filter:Function = null, hitArea:InteractiveObject=null)
		{
			_hit = hitArea;
			this.filter = filter;
			attach(container);
		}
		
		/** @inheritDoc */
		public override function attach(obj:InteractiveObject):void
		{
			if (obj==null) { detach(); return; }
			if (!(obj is DisplayObjectContainer)) {
				throw new Error("Attached object must be a DisplayObjectContainer");
			}
			super.attach(obj);
			if (obj != null) {
				obj.addEventListener(Event.ADDED_TO_STAGE, onAdd);
				obj.addEventListener(Event.REMOVED_FROM_STAGE, onRemove);
				if (obj.stage != null) onAdd();
			}
		}
		
		/** @inheritDoc */
		public override function detach():InteractiveObject
		{
			onRemove();
			if (_object != null) {
				_object.removeEventListener(Event.ADDED_TO_STAGE, onAdd);
				_object.removeEventListener(Event.REMOVED_FROM_STAGE, onRemove);
			}
			_hit = null;
			return super.detach();
		}
		
		private function onAdd(evt:Event=null):void
		{
			_stage = _object.stage;
			if (_hit == null) _hit = _stage;
			_hit.addEventListener(MouseEvent.MOUSE_DOWN, mouseDown);
			DisplayObjectContainer(_object).addChild(_shape);
		}
		
		private function onRemove(evt:Event=null):void
		{
			if (_hit)
				_hit.removeEventListener(MouseEvent.MOUSE_DOWN, mouseDown);
			if (_object)
				DisplayObjectContainer(_object).removeChild(_shape);
		}
		
		private function mouseDown(evt:MouseEvent):void
		{
			if (_stage == null) return;
			_stage.addEventListener(MouseEvent.MOUSE_UP, mouseUp);
			_stage.addEventListener(MouseEvent.MOUSE_MOVE, mouseMove);
			
			_r.x = _object.mouseX;
			_r.y = _object.mouseY;
			_r.width = 0;
			_r.height = 1;
			_drag = true;
			renderShape();
			selectionTest();
		}
		
		private function mouseMove(evt:MouseEvent):void
		{
			if (!_drag) return;
			_r.width = _object.mouseX - _r.x;
			_r.height = _object.mouseY - _r.y;
			renderShape();
			selectionTest();
		}
		
		private function mouseUp(evt:MouseEvent):void
		{
			_drag = false;
			_shape.graphics.clear();
			_stage.removeEventListener(MouseEvent.MOUSE_UP, mouseUp);
			_stage.removeEventListener(MouseEvent.MOUSE_MOVE, mouseMove);
		}
		
		private function renderShape():void {
			var g:Graphics = _shape.graphics;
			g.clear();
			
			g.beginFill(0x999999, 0.3);
			g.lineStyle(1, 0x999999, 0.4, true, "none");
			g.drawRect(_r.x, _r.y, _r.width, _r.height);
			g.endFill();
		}
		
		private function selectionTest():void {
			var con:DisplayObjectContainer = DisplayObjectContainer(_object);
			for (var i:uint=0; i<con.numChildren; ++i) {
				walkTree(con.getChildAt(i), selTest);
			}
		}
		
		private static function walkTree(obj:DisplayObject, func:Function):void
		{
			func(obj);
			if (obj is DisplayObjectContainer) {
				var con:DisplayObjectContainer = obj as DisplayObjectContainer;
				for (var i:int=0; i<con.numChildren; ++i) {
					walkTree(con.getChildAt(i), func);
				}
			}
		}
		
		private function selTest(d:DisplayObject):void
		{
			if (filter!=null && !filter(d)) return;
			
			if (d.hitTestObject(_shape)) {
				if (onSelect != null) onSelect(d);
			} else {
				if (onDeselect != null) onDeselect(d);
			}
		}
		
	} // end of class CustomSelectionControl
}