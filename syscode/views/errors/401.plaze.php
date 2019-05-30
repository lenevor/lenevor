<?= $this->extend('errors.layout') ?>

<?= $this->section('title') ?>

		<title><?= __('Unauthorized') ?></title>

<?= $this->stop() ?>

<?= $this->section('code') ?>

				<h1>401</h1>

<?= $this->stop() ?>

<?= $this->section('message') ?>

				<?= __($exception->getMessage() ?: 'Unauthorized') ?>

<?= $this->stop() ?>