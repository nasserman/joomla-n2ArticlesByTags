<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
 
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');
 
// The class name must always be the same as the filename (in camel case)
class JFormFieldn2tags extends JFormFieldList {
 
	//The field class must know its own type through the variable $type.
	protected $type = 'n2tags';
 
//	public function getLabel() {
//		// code that returns HTML that will be shown as the label
//	}
        
	public function getOptions()
	{
		// Initialize variables.
		$options = array();
 
		$db	= JFactory::getDbo();
		$query	= $db->getQuery(true);
 
		$query->select('id As value, title As text');
		$query->from('#__tags AS a');
		$query->order('a.title');
		$query->where("published = 1 and title !='ROOT'");
 
		// Get the options.
		$db->setQuery($query);
 
		$options = $db->loadObjectList();
 
		// Check for a database error.
		if ($db->getErrorNum()) {
			JError::raiseWarning(500, $db->getErrorMsg());
		}
 
		return $options;
	}        
 
//	public function getInput() {
//            // code that returns HTML that will be shown as the form field
//            $output = '<select id="'.$this->id.'" name="'.$this->name.'">';
//            $db = JFactory::getDbo();
//            $query="SELECT id, title AS title FROM `#__tags` WHERE published=1 AND title!='ROOT'";
//            $db->setQuery($query);
//            $results = $db->loadObjectList();
//            foreach($results as $row){
//                $output .= '<option value="'.$row->id.'" >'.$row->title.'</option>';
//            }
//            $output .= '</select>';
//            return $output;
//	}
}