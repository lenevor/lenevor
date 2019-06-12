<footer>

	<div class="info">

		<div class="name">
			<?= e($brand) ?>
			<span class="status-code" title="Status Code"><?= $debug->sendHttpCode() ?></span>
			<span class="php-version" title="php version"><?= phpversion() ?></span>
			<span class="benchmark" title="Benchmark">{elapsed_time}</span>
			
		</div>
		
		<div class="copy">
			Displayed at <?= date('H:i:sa') ?> &mdash;			
			<?= \Syscode\Version::shortVersion() ?>
		</div>

	</div>

</footer>