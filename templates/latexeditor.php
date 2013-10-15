<input type="hidden" name="dir" value="<?php p($_['dir']) ?>" id="dir">
<input type="hidden" name="padID" value="<?php p($_['padID']) ?>" id="padID">
<div id="controls">
	<?php print_unescaped($_['breadcrumb']); ?>
	<div class="actions">
		<div id="latexpad_compile" class="button">
			<a><?php p($l->t('Compile Project'));?></a>
		</div>
	</div>
</div>
<div id="latexpad_wrapper" class="latexpad_box">
	<div id="latexpad_col_1" class="latexpad_box latexpad_col">
		<div id="latexpad_pad" class="latexpad_box latexpad_cell">
			<iframe src='<?php p($_['etherpad_server']); ?>/p/<?php p($_['padID']); ?>?userName=<?php p($_['user']); ?>&showChat=false&alwaysShowChat=false&showLineNumbers=true&showControls=true&useMonospaceFont=true' width="100%" height="100%"></iframe>
		</div>
		<div id="latexpad_hspliter" class="latexpad_box latexpad_cell"></div>
		<div id="latexpad_log" class="latexpad_box latexpad_cell">
			<pre></pre>
		</div>
	</div>
	<div id="latexpad_vspliter" class="latexpad_box latexpad_col"></div>
	<div id="latexpad_col_2" class="latexpad_box latexpad_col">
		<div id="latexpad_preview" class="latexpad_box latexpad_cell">
		</div>
	</div>
</div>
