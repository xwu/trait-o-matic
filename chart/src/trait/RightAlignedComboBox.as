package trait
{
	// an extended version of the Flex framework ComboBox class
	// based on code at http://onreflexion.blogspot.com/ (Scrawl classes for Napkin theme)
    import mx.controls.ComboBox;
    import mx.core.IFactory;
    import mx.core.ClassFactory;

    public class RightAlignedComboBox extends ComboBox
    {
        private var _ddFactory:IFactory = new ClassFactory(RightAlignedComboBoxDropdown);    

        override public function get dropdownFactory():IFactory
        {
            return _ddFactory;
        }        

        override public function set dropdownFactory(factory:IFactory):void
        {
            _ddFactory = factory;
        }
    }
}