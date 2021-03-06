<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_tags_popular
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Helper for mod_tags_popular
 *
 * @package     Joomla.Site
 * @subpackage  mod_tags_popular
 * @since       3.1
 */
abstract class ModN2ArticlesByTagsHelper
{
	public static function getContentList($params)
	{
		$db         = JFactory::getDbo();
		$app        = JFactory::getApplication();
		$user       = JFactory::getUser();
		$groups     = implode(',', $user->getAuthorisedViewLevels());
		//$matchtype  = $params->get('matchtype', 'all');
		$maximum    = $params->get('maximum', 5);
		$tagsHelper = new JHelperTags;
                $n2tagsHelper = new n2tags();
		$option     = $app->input->get('option');
		$view       = $app->input->get('view');
		$prefix     = $option . '.' . $view;
		$id         = (array) $app->input->getObject('id');
		$catids = implode(',',$params->get('catids'));
                $includecatchilds = $params->get('includecatchildren')==1 ? true : false ;
                $selectedTags = $params->get('selectedtags');
             
		// Strip off any slug data.
		foreach ($id as $id)
		{
                    if (substr_count($id, ':') > 0)
                    {
                            $idexplode = explode(':', $id);
                            $id        = $idexplode[0];
                    }
		}

                //$tagsToMatch = $selectedTag;
                $tagsToMatch =  implode(',',$selectedTags) ;
                if (!$tagsToMatch || is_null($tagsToMatch))
                {
                    return $results = false;
                }
                //var_dump($tagsToMatch);

                $_anyorall = FALSE;
                if($params->get('anyorall')==1){
                    $_anyorall = TRUE;
                }
                
                $includechildren = FALSE;
                if($params->get('includechildren')==1){
                    $includechildren = TRUE;
                }
                //var_dump($_anyorall);
                
                //$query=$tagsHelper->getTagItemsQuery($tagsToMatch, $typesr = null, $includeChildren = $includechildren, $orderByOption = $params->get('orderbyoption'), $orderDir = $params->get('orderdir'),$anyOrAll = $_anyOrAll, $languageFilter = 'all', $stateFilter = '0,1');
                //$query=$tagsHelper->getTagItemsQuery($tagsToMatch, null, $includeChildren = $includechildren, $params->get('orderbyoption'), $params->get('orderdir'),$anyOrAll = $_anyorall, 'all',  '0,1');
                //var_dump($tagsToMatch);
                //echo '<hr/>';
                
                $query= $n2tagsHelper->getTagItemsQuery($tagsToMatch, null, $includeChildren = $includechildren, $params->get('orderbyoption'), $params->get('orderdir'),$anyOrAll = $_anyorall, 'all',  '0,1', $catIds =$catids, $includecatchilds);
                //echo '<hr/>'.$query.'<hr/>';
                $db->setQuery($query, 0, $maximum);
                $results = $db->loadObjectList();

                foreach ($results as $result)
                {
                        $explodedAlias = explode('.', $result->type_alias);
                        $result->link = 'index.php?option=' . $explodedAlias[0] . '&view=' . $explodedAlias[1] . '&id=' . $result->content_item_id . '-' . $result->core_alias;
                }

                return $results;
		
	}
}