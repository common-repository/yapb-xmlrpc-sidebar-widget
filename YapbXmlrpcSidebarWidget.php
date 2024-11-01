<?php
	
	/*
	Plugin Name: YAPB XMLRPC Sidebar Widget
	Plugin URI: http://johannes.jarolim.com/yapb/sidebar-widget
	Description: The YAPB Sidebar Widget displays some of your latest images or some random images posted on a blog with <a href="http://johannes.jarolim.com/yapb">YAPB</a> and the <a href="http://wordpress.org/extend/plugins/yapb-xmlrpc-server">YAPB XMLRPC Server</a>.
	Author: J.P.Jarolim
	License: GPL
	Version: 1.2
	Author URI: http://johannes.jarolim.com
	*/

	/**
	 *
	 * The YAPB XMLRPC Sidebar Widget displays either some of your latest or some random images from
	 * your PhotoBlog. To display your images, you have to have an active YAPB + YAPB XMLRPC Server 
	 * installation on another blog.
	 *
	 * Remote access via XML-RPC to another blog
	 * (1) Activate the plugin, go to the Design/Widgets Page, add the widget to your Sidebar and
	 * (2) Provide the URL to the blog runnig YAPB and the set API key
	 * (3) Edit it's configuration.
	 * (4) Done!
	 *
	 **/

	/* Short and sweet */

	require_once 'YapbXmlrpcSidebarWidget.class.php';
	$yapbXmlrpcSidebarWidget = new YapbXmlrpcSidebarWidget();

?>