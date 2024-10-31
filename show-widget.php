<?php
// This file pulls down the widget content from the cdn so it can be used on the same domain

if (isset($_GET['code'])) {
    // Build the URL for the widget and get its contents
    $widget = file_get_contents('http://cdn.oddswidget.com/widgets/'.$_GET['code'].'.html?t='.time());
} else {
    $widget = 'There has been an error, the widget cannot be displayed.';
}

echo $widget;