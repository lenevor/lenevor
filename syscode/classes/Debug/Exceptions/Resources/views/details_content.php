<div class="details">

	<div class="data-table-container">
	<?php foreach ($tables as $label => $data) : ?>
		<div class="data-table">
			<?php if ( ! empty($data)) : ?>
			<label><?= e($label) ?></label>
			<table class="data-table">
				<thead>
					<tr>
						<td>Key</td>
						<td>Value</td>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($data as $key => $value) : ?>
					<tr>
						<td><?= e($key) ?></td>
						<td><?= e(print_r($value, true)) ?></td>
					</tr>
				<?php endforeach; ?>	
				</tbody>
			</table>
			<?php else : ?>
			<label class="empty"><?= e($label) ?></label>
			<span class="empty"><?= e(__('exception.empty')) ?></span>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>
	</div>

	<div class="data-table-container">
		<label><?= e(__('exception.RegisteredHandlers')) ?></label>
			<div class="data-table">
			<?php foreach ($handlers as $i => $handler) : ?>
				<div class="debug-list"><?= $i + 1 ?>.&nbsp;&nbsp;<?= e(get_class($handler)) ?></div>
			<?php endforeach; ?>
			</div>
	</div>

</div>

