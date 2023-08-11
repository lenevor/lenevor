<header>
	<div class="margins">
		<div class="container">
			<nav>
				<ul>
					<li>
						<div class="container-title">
							<i class="icon-stack"></i>
							<a href="#stack" target="_self" class="">
								<?= e(__('exception.stack')) ?>
							</a>
						</div>
					</li>
					<li>
						<div class="container-title">
							<i class="icon-detail"></i>
							<a href="#detail-request" target="_self" class="">
								<?= e(__('exception.details')) ?>
							</a>
						</div>
					</li>
				</ul>
			</nav>
		</div>
		<div class="time">
			<div class="message">
				<div class="container">
					<a href="#top"><?= ucfirst($template->escape($message)) ?></a>
				</div>
			</div>
		</div>
	</div>
</header>