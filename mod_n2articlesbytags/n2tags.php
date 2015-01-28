<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of n2tags
 *
 * @author Sammy Guergachi <sguergachi at gmail.com>
 */
class n2tags extends JHelperTags {

    /**
    * Method to get a query to retrieve a detailed list of items for a tag.
    *
    * @param   mixed    $tagId            Tag or array of tags to be matched
    * @param   mixed    $typesr           Null, type or array of type aliases for content types to be included in the results
    * @param   boolean  $includeChildren  True to include the results from child tags
    * @param   string   $orderByOption    Column to order the results by
    * @param   string   $orderDir         Direction to sort the results in
    * @param   boolean  $anyOrAll         True to include items matching at least one tag, false to include
    *                                     items all tags in the array.
    * @param   string   $languageFilter   Optional filter on language. Options are 'all', 'current' or any string.
    * @param   string   $stateFilter      Optional filtering on publication state, defaults to published or unpublished.
    *
    * @return  JDatabaseQuery  Query to retrieve a list of tags
    *
    * @since   3.1
    */
   public function getTagItemsQuery($tagId, $typesr = null, $includeChildren = false, $orderByOption = 'c.core_title', $orderDir = 'ASC',
           $anyOrAll = true, $languageFilter = 'all', $stateFilter = '0,1', $catIds=null, $includecatchilds=false)
   {
           // Create a new query object.
           $db = JFactory::getDbo();
           $query = $db->getQuery(true);
           $user = JFactory::getUser();
           $nullDate = $db->quote($db->getNullDate());
           $nowDate = $db->quote(JFactory::getDate()->toSql());

           $ntagsr = substr_count($tagId, ',') + 1;

           // Force ids to array and sanitize
           $tagIds = (array) $tagId;
           $tagIds = implode(',', $tagIds);
           $tagIds = explode(',', $tagIds);
           JArrayHelper::toInteger($tagIds);

           // If we want to include children we have to adjust the list of tags.
           // We do not search child tags when the match all option is selected.
           if ($includeChildren)
           {
                   $tagTreeList = '';
                   $tagTreeArray = array();

                   foreach ($tagIds as $tag)
                   {
                           $this->getTagTreeArray($tag, $tagTreeArray);
                   }

                   $tagIds = array_unique(array_merge($tagIds, $tagTreeArray));
           }

           // Sanitize filter states
           $stateFilters = explode(',', $stateFilter);
           JArrayHelper::toInteger($stateFilters);

           // M is the mapping table. C is the core_content table. Ct is the content_types table.
           $query->select('m.type_alias, m.content_item_id, m.core_content_id, count(m.tag_id) AS match_count,  MAX(m.tag_date) as tag_date, MAX(c.core_title) AS core_title')
                   ->select('MAX(c.core_alias) AS core_alias, MAX(c.core_body) AS core_body, MAX(c.core_state) AS core_state, MAX(c.core_access) AS core_access')
                   ->select('MAX(c.core_metadata) AS core_metadata, MAX(c.core_created_user_id) AS core_created_user_id, MAX(c.core_created_by_alias) AS core_created_by_alias')
                   ->select('MAX(c.core_created_time) as core_created_time, MAX(c.core_images) as core_images')
                   ->select('CASE WHEN c.core_modified_time = ' . $nullDate . ' THEN c.core_created_time ELSE c.core_modified_time END as core_modified_time')
                   ->select('MAX(c.core_language) AS core_language, MAX(c.core_catid) AS core_catid')
                   ->select('MAX(c.core_publish_up) AS core_publish_up, MAX(c.core_publish_down) as core_publish_down')
                   ->select('MAX(ct.type_title) AS content_type_title, MAX(ct.router) AS router')

                   ->from('#__contentitem_tag_map AS m')
                   ->join('INNER', '#__ucm_content AS c ON m.type_alias = c.core_type_alias AND m.core_content_id = c.core_content_id AND c.core_state IN (' . implode(',', $stateFilters) . ')' . (in_array('0', $stateFilters) ? '' : ' AND (c.core_publish_up = ' . $nullDate . ' OR c.core_publish_up <= ' . $nowDate . ') AND (c.core_publish_down = ' . $nullDate . ' OR  c.core_publish_down >= ' . $nowDate . ')'))
                   ->join('INNER', '#__content_types AS ct ON ct.type_alias = m.type_alias')

                   // Join over the users for the author and email
                   ->select("CASE WHEN c.core_created_by_alias > ' ' THEN c.core_created_by_alias ELSE ua.name END AS author")
                   ->select("ua.email AS author_email")

                   ->join('LEFT', '#__users AS ua ON ua.id = c.core_created_user_id')

                   ->where('m.tag_id IN (' . implode(',', $tagIds) . ')');

           // Optionally filter on language
           if (empty($language))
           {
                   $language = $languageFilter;
           }

           if ($language != 'all')
           {
                   if ($language == 'current_language')
                   {
                           $language = $this->getCurrentLanguage();
                   }

                   $query->where($db->quoteName('c.core_language') . ' IN (' . $db->quote($language) . ', ' . $db->quote('*') . ')');
           }

           // Get the type data, limited to types in the request if there are any specified.
           //$typesarray = self::getTypes('assocList', $typesr, false);
           $typesarray = self::getTypes('assocList', $typesr, false);

           $typeAliases = '';

           foreach ($typesarray as $type)
           {
                   $typeAliases .= "'" . $type['type_alias'] . "'" . ',';
           }

           $typeAliases = rtrim($typeAliases, ',');
           $query->where('m.type_alias IN (' . $typeAliases . ')');

           $groups = '0,' . implode(',', array_unique($user->getAuthorisedViewLevels()));
           
           if($catIds){
               if(!$includecatchilds){
                    $_catIds = $catIds;
               }else {
                    $cats = array();
                    $a2 = explode(',', $catIds);
                    while (count($a2)>0){
                        $query_str = " select * from #__categories where parent_id in (".  implode(',', $a2).") and published = 1 and title !='ROOT'" ;
                        $db2 = JFactory::getDbo();
                        $db2->setQuery($query_str);  
                        $a3 = $db2->loadObjectList();
                        foreach($a2 as $item1){
                            $cats[$item1] = $item1;
                        }
                        $a2 = array();
                        foreach($a3 as $item2){
                            $a2[$item2->id] = $item2->id;
                        }
                    }
                    $_catIds = implode(',', $cats);                   
               }
               $query->where('c.core_catid IN ('.$_catIds.')');
           }
           
           $query->where('c.core_access IN (' . $groups . ')')
                   ->group('m.type_alias, m.content_item_id, m.core_content_id');
           

           // Use HAVING if matching all tags and we are matching more than one tag.
           //if ($ntagsr > 1 && $anyOrAll != 1 && $includeChildren != 1)
           if ($ntagsr > 1 && $anyOrAll != 1 && $includeChildren != 1)
           {
                   // The number of results should equal the number of tags requested.
                   $query->having("COUNT('m.tag_id') = " . (int) $ntagsr);
           }
           //$query->having("'core_catid' = 9" );


           // Set up the order by using the option chosen
           if ($orderByOption == 'match_count')
           {
                   $orderBy = 'COUNT(m.tag_id)';
           }
           else
           {
                   $orderBy = 'MAX(' . $db->quoteName($orderByOption) . ')';
           }

           $query->order($orderBy . ' ' . $orderDir);

           return $query;
   }


}
