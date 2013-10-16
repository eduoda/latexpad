LaTeX Pad
=========
Tools for collaborative LaTeX documents.
* Create a new database
* Download the OwnCloud tar ball and explod it into your htdocs
* Create the directories and give the right permissions:
<pre><code>$ mkdir data
	$ mkdir apps2
	$ chown -R apache:apache apps/ config/ data apps2
</code></pre>
* Access your server and follow the instalations instructions, selecting MySQL in the advanced configs
* Edit your OWNCLOUD_DIR/config/config.php
<pre><code>'appstoreenabled' => true,
	'appstoreurl' => 'http://api.apps.owncloud.com/v1',
	'apps_paths' => array (
		0 =>
		array (
			'path' => 'OWNCLOUD_DIR/apps',
			'url' => '/apps',
			'writable' => false,
		),
		1 =>
		array (
			'path' => 'OWNCLOUD_DIR/apps2',
			'url' => '/apps2',
			'writable' => true,
		),
	)
</code></pre>
* Edit the OWNCLOUD_DIR/lib/mimetypes.list.php and add the line:
	<pre><code>'tex' => 'application/x-tex'</code></pre>
* Enter in the apps2 directory and clone the app:
	<pre><code>$ git clone https://github.com/eduoda/latexpad.git</code></pre>
* Go to "Application" interface and enable the LaTeX Pad app
* Go to the "Admin" interface and configure the APIKEY and the Etherpad Server.
* Install the etherpad
* Go to ETHERPAD_DIR/node_modules and clone the plugin:
	<pre><code>$ git clone https://github.com/eduoda/latexpad.git</code></pre>
* Access the etherpad admin page and check if the plugin is enabled. 
* Done!

Using LaTeX Pad
===============
* Log in the owncloud
* Click on New -> LaTeX Project
* Type the project name and [ENTER]
* Enter in the directory
* Click the latex file to edit
* If you need another latex document click New->Text file
* Enter the file name with a .tex extension then [ENTER]
* The pdf generation os done every 10 seconds if the source has changed
* You can also click on [Compile Project]
* New pages are loaded and they blink 3 times (it's very annoying)
