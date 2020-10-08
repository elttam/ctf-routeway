<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<div class="box">
	<p>
		A joint knockout with high availability not applicable, if they find the wrong route.
	</p
	<p>&nbsp;</p>

<?php foreach ($links as $title => $url): ?>
       <?php echo html::anchor($url, html::chars(__($title))) ?>&nbsp;:&nbsp;
<?php endforeach ?>
</p>
</div>
