<?php

	/*	Copyright 2008 J.P.Jarolim (email : yapb@johannes.jarolim.com)

		This program is free software; you can redistribute it and/or modify
		it under the terms of the GNU General Public License as published by
		the Free Software Foundation; either version 2 of the License, or
		(at your option) any later version.

		This program is distributed in the hope that it will be useful,
		but WITHOUT ANY WARRANTY; without even the implied warranty of
		MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
		GNU General Public License for more details.

		You should have received a copy of the GNU General Public License
		along with this program; if not, write to the Free Software
		Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
	*/

	/**
	 * Class YapbSidebarWidget
	 *
	 * TODOs: 
	 * - Rename all options so they don't collide with the local YAPB Sidebar Widget
	 *
	 **/

	class YapbXmlrpcSidebarWidget {

		/**
		 * This boolean variable indicates if this 
		 * sidebar widget is activated on a blog
		 * with an activated YAPB installation
		 **/

		var $isLocal;

		/**
		 * Options for this widget
		 **/

		var $options;

		/**
		 * Constructor
		 **/
		function YapbXmlrpcSidebarWidget() {

			add_action('plugins_loaded', array(&$this, 'onPluginsLoaded'));

		}

		/**
		 * This method registers the widget right after 
		 * all plugins have loaded
		 **/
		function onPluginsLoaded() {

			// Let's generate the options

			$this->options = $this->_getOptions();

			// Lets register widget and control for XMLRPC access

			$id = 'yapb-xmlrpc-sidebar-widget'; // Never never never translate an id
			$name = 'YAPB XMLRPC Sidebar Widget';

			register_sidebar_widget(
				'YAPB XMLRPC Sidebar Widget', 
				array(&$this, 'onDrawXmlrpc')
			);

			register_widget_control(
				'YAPB XMLRPC Sidebar Widget',
				array(&$this, 'onControlXmlrpc'),
				640
			);

			add_action('admin_head', array(&$this, 'onAdminHead'), '', 0);

		}

		/**
		 * This method adds some spicy CSS 
		 * to the admin panel
		 **/
		function onAdminHead() {

			// Let's adapt to the widgets page

			if( strstr($_SERVER['PHP_SELF'], 'widgets.php') === FALSE ) return;

			echo '<style type="text/css">

				table.form-table {
					margin-bottom:10px;
				}

				table.form-table th, table.form-table td {
					border-bottom-color:#cfebf7;
					background-color:#e4f7ff;
				}

				table.form-table ul {
					margin:0;
					padding:0;
				}

				table.form-table ul li {
					list-style-type:none;
					margin:0 0 4px 0;
					padding:0;
				}

			</style>';

		}

		/** 
		 * This method draws the controls for the widget.
		 * We're without an local Yapb Installation and render the full Options tree
		 **/
		function onControlXmlrpc() {

			if($_POST['yapb-xmlrpc-sidebar-widget-submit']) {
				$this->options->update();
			}

			// Output the options
			echo '<input type="hidden" id="yapb-xmlrpc-sidebar-widget-submit" name="yapb-xmlrpc-sidebar-widget-submit" value="1" />';

			echo $this->options->toString();

		}


		//
		// Get data and display it
		//

		function onDrawXmlrpc(&$args) {

			// We use the IXR XML RPC Library which is included with WordPress

			require_once ABSPATH . WPINC . '/class-IXR.php';
			$client = new IXR_Client(get_option('yapb_sidebarwidget_xmlrpc_url') . '/xmlrpc.php');

			// If we have problems with the request,
			// we uncomment on the following two lines

			# $client->debug = true;
			# echo '<pre>'; print_r($client); echo '</pre>';

			$client->query(
				'yapb.getImages',
				array(
					'apikey' => get_option('yapb_sidebarwidget_xmlrpc_apikey'), 
					'order' => get_option('yapb_sidebarwidget_xmlrpc_order'),
					'count' => get_option('yapb_sidebarwidget_xmlrpc_imagecount'),
					$thumb
				)
			);

			$response = &$client->getResponse();
			$data = unserialize(base64_decode($response));

			$this->draw($args, $data);

		}


		/**
		 * This method finally gets things done:
		 * It draws the sidebar widget when called by WP
		 **/
		function draw(&$args, &$data) {

			extract($args);

			$imagecount = get_option('yapb_sidebarwidget_xmlrpc_imagecount');
			$maxsize = get_option('yapb_sidebarwidget_xmlrpc_maxsize');
			$restrict = get_option('yapb_sidebarwidget_xmlrpc_restrict');
			$title = get_option('yapb_sidebarwidget_xmlrpc_title');
			
			switch(get_option('yapb_sidebarwidget_xmlrpc_displayas')) {
				
				case 'ul' :
					
					$beforeBlock = '<ul class="yapb-latest-images">';
					$beforeItem = '<li>';
					$afterItem = '</li>';
					$afterBlock = '</ul>';
					break;
				
				case 'div' :
				default:

					$beforeBlock = '<div class="yapb-latest-images">';
					$beforeItem = '';
					$afterItem = '';
					$afterBlock = '</div>';
					break;
					
			}
			
			echo $before_widget;

			// Output the title

			if (trim($title) != '') {
				echo $before_title . $title . $after_title;
			}

			// Let's define the thumbnail configuration parameters
			// needed for the request

			$thumb = array();
			$thumb[] = 'q=100';
			$restrict = get_option('yapb_sidebarwidget_xmlrpc_restrict');
			switch ($restrict) {
				case 'h':
					$thumb[] = 'w=' . get_option('yapb_sidebarwidget_xmlrpc_maxsize'); 
					break;
				case 'v': 
					$thumb[] = 'h=' . get_option('yapb_sidebarwidget_xmlrpc_maxsize'); 
					break;
				case 'b':
					$thumb[] = 'w=' . get_option('yapb_sidebarwidget_xmlrpc_maxsize');
					$thumb[] = 'h=' . get_option('yapb_sidebarwidget_xmlrpc_maxsize');
					$thumb[] = 'zc=1';
					break;
			}
			
			if (!empty($data)) {

				echo $beforeBlock;

				// The default loop direction

				$loop_start = 0;
				$loop_end = count($data);
				$loop_inc = 1;

				if (get_option('yapb_sidebarwidget_xmlrpc_reverse')) {

					// User wants the direction reversed

					$loop_start = count($data)-1;
					$loop_end = -1;
					$loop_inc = -1;

				}

				// The image loop

				for($i=$loop_start; $i!=$loop_end; $i+=$loop_inc) {
					$item = $data[$i];
					echo $beforeItem . '<a title="' . $item['post.title'] . '" style="border:0;padding:0;margin:0;" href="' . $item['post.url'] . '"><img border="0" style="padding-right:2px;padding-bottom:2px;" src="' . $item['img.url'] . '" width="' . $item['img.width'] . '" height="' . $item['img.height'] . '" alt="' . $item['post.title'] . '" /></a>' . $afterItem;
				}

				echo $afterBlock;

				if (get_option('yapb_sidebarwidget_xmlrpc_link_activate')) {
					echo '<a class="yapb-latest-images-link" href="' . get_option('yapb_sidebarwidget_xmlrpc_link_mosaic') . '">' . get_option('yapb_sidebarwidget_xmlrpc_link_mosaic_title') . '</a>';
				}

			} else {

				// Sorry, no images
				echo '<p class="yapb-no-latest-images">';
				echo 'Sorry: nothing yet';
				echo ' - Maybe wrong XMLRPC URL or API key?';
				echo '</p>';

			}

			echo $after_widget;

		}


		function _getOptions() {

			require_once realpath(dirname(__file__) . '/lib/YapbCheckboxInputOption.class.php');
			require_once realpath(dirname(__file__) . '/lib/YapbCheckboxOption.class.php');
			require_once realpath(dirname(__file__) . '/lib/YapbCheckboxSelectOption.class.php');
			require_once realpath(dirname(__file__) . '/lib/YapbExifTagnamesOption.class.php');
			require_once realpath(dirname(__file__) . '/lib/YapbInputOption.class.php');
			require_once realpath(dirname(__file__) . '/lib/YapbSelectOption.class.php');
			require_once realpath(dirname(__file__) . '/lib/YapbTextareaOption.class.php');
			require_once realpath(dirname(__file__) . '/lib/YapbOptionGroup.class.php');
			require_once realpath(dirname(__file__) . '/lib/Params.class.php');

			// Build the options array

			$result = new YapbOptionGroup(
				'YAPB Sidebar Widget',
				'',
				array(

					new YapbOptionGroup(
						'XML RPC Credentials',
						'Please provide the URL of your WordPress blog with activated YAPB + YAPB-XMLRPC-Server Plugin to access your images remotely.',
						array(
							new YapbInputOption('yapb_sidebarwidget_xmlrpc_url', __('URL #40 /xmlrpc.php', 'yapb'), 'http://www.yourdomain.tld/blogdir'),
							new YapbInputOption('yapb_sidebarwidget_xmlrpc_apikey', __('API key #40', 'yapb'), 'Your API key')
						)
					),

					new YapbOptionGroup(
						__('Widget Configuration', 'yapb'), 
						'',
						array(
							new YapbInputOption('yapb_sidebarwidget_xmlrpc_title', __('Widget Title: #20 Leave empty if you don\'t want to display a title.', 'yapb'), 'Latest photography'),
							new YapbSelectOption('yapb_sidebarwidget_xmlrpc_displayas', __('#', 'yapb'), array('Display thumbnails as bunch of linked images in a div container' => 'div','Display thumbnails as bunch of list items in an unordered list' => 'ul'), 'div'),
							new YapbSelectOption('yapb_sidebarwidget_xmlrpc_order', __('#'), array('The widget displays a list of your latest images'=>'latest', 'The widget displays some random images'=>'random'), 'latest'),
							new YapbCheckboxOption('yapb_sidebarwidget_xmlrpc_reverse', __('Display images in reverse order', 'yapb'), false),
							new YapbInputOption('yapb_sidebarwidget_xmlrpc_imagecount', __('Show #10 images.', 'yapb'), '5'),

							new YapbInputOption('yapb_sidebarwidget_xmlrpc_maxsize', __('Maximal thumbnail size: #10 px.', 'yapb'), ''),
							new YapbSelectOption('yapb_sidebarwidget_xmlrpc_restrict', __('Restrict thumbnail size #.', 'yapb'), array('horizontally'=>'h','vertically'=>'v', 'both'=>'b'), 'vertically'),

						)
					),

					new YapbOptionGroup(
						__('Mosaic link', 'yapb'), 
						'',
						array(
							new YapbCheckboxOption('yapb_sidebarwidget_xmlrpc_link_activate', __('Show Link to mosaic page', 'yapb'), false),
							new YapbInputOption('yapb_sidebarwidget_xmlrpc_link_mosaic', __('Link to mosaic page: #40 (optional)', 'yapb'), ''),
							new YapbInputOption('yapb_sidebarwidget_xmlrpc_link_mosaic_title', __('Title of mosaic page link: #20', 'yapb'), 'All images')
						)
					)

				)
			);

			// Additionally, we do some configuration

			$result->setLevel(1);
			$result->initialize();
			return $result;

		}


	}

?>
