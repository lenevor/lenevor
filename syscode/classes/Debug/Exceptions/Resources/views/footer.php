<footer>

	<div class="info">

		<div class="name">
			<?= e($brand) ?>
			<span class="status-code" title="Status Code"><?= (new \Syscode\Debug\GDebug)->sendHttpCode() ?></span>
			<span class="php-version" title="php version"><?= phpversion() ?></span>
			
		</div>
		
		<div class="copy">
			Displayed at <?= date('H:i:sa') ?> &mdash;			
			<?= \Syscode\Version::shortVersion() ?>
		</div>

	</div>

</footer>