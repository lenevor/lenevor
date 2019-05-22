<footer>

	<div class="info">

		<div class="name">
			<?= e($brand) ?>
		</div>

		<div class="copy">
			Displayed at <?= date('H:i:sa') ?> &mdash;
			PHP: <?= phpversion() ?>  &mdash;
			<?= \Syscode\Version::shortVersion() ?>
		</div>

	</div>

</footer>