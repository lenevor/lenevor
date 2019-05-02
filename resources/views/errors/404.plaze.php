<?= $this->extend('layouts.error') ?>

<?= $this->section('content') ?>

		<div class='content'>

			<div id="js-menu">
				<div class="menu"></div>			
			</div> 

			<div class="container">
				
				<h1>
					<?= __('Page Not Found') ?>
				</h1>

				<?= $this->insert('partials.footer') ?>

			</div>

		</div>

<?= $this->stop() ?>