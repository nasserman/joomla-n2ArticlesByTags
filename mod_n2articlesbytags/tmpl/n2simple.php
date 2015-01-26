<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_tags_popular
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>
<?php JLoader::register('TagsHelperRoute', JPATH_BASE . '/components/com_tags/helpers/route.php'); ?>
<div class="n2articlesbytags<?php echo $moduleclass_sfx; ?>">
<?php if ($list) : ?>
    
    
    <!-- n2 simple start ------------------------------------------------------>
    <?php 
        $count = count($list);
        $first_item = $list[0];
        $first_item->route = new JHelperRoute;
        $link = JRoute::_(TagsHelperRoute::getItemRoute($first_item->content_item_id, $first_item->core_alias, $first_item->core_catid, $first_item->core_language, $first_item->type_alias, $first_item->router));
        $image_url = json_decode($first_item->core_images)->image_intro;
        //var_dump($first_item);
        //echo '<hr/>';
    ?>
    <div class="n2simple">
        <div class="n2simple-cola">
            <a href="<?php echo $link; ?>">
                <img src="<?php echo $image_url; ?>"/>
            </a>
                
            <a href="<?php echo $link; ?>">
                <h3><?php if (!empty($first_item->core_title)){
                        echo htmlspecialchars(strip_tags($first_item->core_title));}
                    ?></h3>
            </a>
            <div class="n2simple-body">
            <?php echo htmlspecialchars(strip_tags($first_item->core_body)); ?>
            </div>
        </div>
        
        <div class="n2simple-colb">
            <?php for($k=1;$k<count($list);$k++):?>
            <?php 
            $item = $list[$k];
            $item->route = new JHelperRoute;
            $link = JRoute::_(TagsHelperRoute::getItemRoute($item->content_item_id, $item->core_alias, $item->core_catid, $item->core_language, $item->type_alias, $item->router)); 
            //$image_url = json_decode($item->core_images)->image_intro;
            ?>
            <div class="n2simple-row">
                <a href="<?php echo $link; ?>">
<!--                    <img src="<?php echo $image_url; ?>"/>-->
                    <h4><?php echo ' -  '.htmlspecialchars($item->core_title); ?></h4>
                </a>
            </div>            
            <?php endfor; ?>
        </div>
    </div>
    <!-- n2 simple end -------------------------------------------------------->
    <?php else : ?>
        <span><?php echo JText::_('MOD_TAGS_SIMILAR_NO_MATCHING_TAGS'); ?></span>
    <?php endif; ?>
</div>
<?php
echo "<div style='text-align: center;font-size: 9px;'>";
echo 'تاریخ ایجاد ماژول : ',JHtml::date(new JDate(),'y-m-d g:i a');
echo "</div>";
?>



<style>
.n2simple{}
.n2articlesbytags{}
.n2simple-body{text-align: justify;}
.n2simple-cola{display: inline-block;vertical-align: top;width: 45%;}
.n2simple-colb{  border-right: 1px solid #aaa;
    display: inline-block;
    margin-right: 10px;
    padding-right: 10px;
    vertical-align: top;
    width: 45%;}
.n2simple-row{}
.n2simple-cola img {
    max-height: 250px;
    max-width: 100%;
}
.n2simple-row img {
    display: inline;
    max-height: 70px;
    max-width: 80px;
    vertical-align: middle;
}
.n2simple-row h4 {
    font-size: 1.1em;
    margin-bottom: 8px;
    text-align: justify;
}
</style>