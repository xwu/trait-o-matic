package trait
{
    import mx.controls.List;
    import mx.core.EventPriority;
    import mx.events.MoveEvent;
        
    public class RightAlignedComboBoxDropdown extends List
    {   
    	public function RightAlignedComboBoxDropdown()
    	{
    		super();
    		this.addEventListener(MoveEvent.MOVE, moveHandler, false, EventPriority.DEFAULT);
	   	}
		
		// shifts the menu up to align properly	    
	    private function moveHandler(event:MoveEvent):void
	    {
	    	this.y -= this.width;
	    }	    
    }
}