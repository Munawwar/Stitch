##Stitch - Fast Template Inheritance

Most websites out there have multiple pages using a single template.
Maintaining web pages as raw .html pages are more difficult as number of pages increase, since any change to the template must be manually edited on all pages.

Template inheritance seeks to solve this problem by keeping your template and page contents in separate files.
When a user visits your page, a script is called which stitches the page and template togeather, and serves the final page.

####Supported PHP versions
Tested on 5.3.10 and 5.2.

###Usage with an example
####Quick Start

Template.php

```php
<?php include('stitch.php'); ?>
<!DOCTYPE html>
<html>
	<head>
	</head>
	<body>
		<div style="background-color:#f0f0f0;">
			<?php defineblock('main-content'); ?>
		</div>
	</body>
</html>
```

HomePage.php

```php
<?php include('Template.php'); ?>

<?php startblock('main-content') ?>
	Here is my home page's main body content.
<?php endblock() ?>
```

####Default content

Modify Template.php:

```php
<div style="background-color:#f0f0f0;">
	<?php startblock('main-content'); ?>
		This is the default content. If page chooses not to override this block, then the default content is shown.
	<?php endblock(); ?>
</div>
```

####Append/Prepend content

Most pages choose to override content. But on some cases, a page may need to append/prepend content.

Template.php

```php
<head>
	...
	<?php startblock('font'); ?>
		<link type="text/css" rel="stylesheet" href="http://fonts.googleapis.com/css?family=Droid+Sans%7COswald:regular,700" />
	<?php endblock(); ?>
</head>
```

Page1.php

```php
<head>
	...
	<?php startblock('font', TI::APPEND); ?>
		<!-- Appends a font -->
		<link type="text/css" rel="stylesheet" href="http://fonts.googleapis.com/css?family=Wendy+One" />
	<?php endblock(); ?>
</head>
```

Page2.php

```php
<head>
	...
	<?php startblock('font'); ?>
		<!-- Doesn't require the Droid Sans font, hence replace it with a different one -->
		<link type="text/css" rel="stylesheet" href="http://fonts.googleapis.com/css?family=Wendy+One" />
	<?php endblock(); ?>
</head>
```

####Macros and Partial Views

Can easily be achieved with plain vanilla PHP.

Page.php

```php
<?php include('macros.php') ?>
<?php
	startblock('side-content');
	//Include a view
	include('side-content.php');
	//Code-generated html
	renderMenu(array('current_page' => 'Home')); //Where renderMenu echos/prints the html for the menu
	endblock();
?>
```

####Performance

Use only on development:

```php
define('TI_PROFILE', true);
```

This will give you the total time taken to process the page as an apache header with 'X-ti-' prefix.
The time shown includes the time taken for php engine to parse the pages.

####Optimization

Some ways to reduce server load and improve page load are 1. to use cache control headers while serving the pages, 2. speed things up with APC.

If you want the output of Stitch on run-time you could use the following function:

```
function getHtml($pathToPage) {
	ob_start();
	include($pathToPage);
	ob_end_flush(); //Ends Stitch
	return ob_get_clean();
}
```

With this you can compile the html to a static .html file and then take that further to use cache control.
Or with dynamic content, cache content with in-memory storage like memcached and
echo them out within the page/template blocks?

There are many ways to do this, so pick what is best for your application.

###Philosophy and Features

Q. Why don't you support nested blocks? Why can't page inherit pages or multiple templates? Why can't a template inherit another template?

A: Philosophical reasons:

1. To keep the code simple and to keep template processing overhead to the minimum.

2. I don't see why would you need them. In most cases, you can organizing your code in a way that you don't need them.

On nesting: If you really need it, you can look into [PHPTI](https://github.com/arshaw/phpti).
