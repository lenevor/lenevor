<?= $this->extend('errors.layout') ?>

<?= $this->section('title') ?>

		<title><?= __('Page Expired') ?></title>

<?= $this->stop() ?>

<?= $this->section('code') ?>

			<h1>419</h1>

<?= $this->stop() ?>

<?= $this->section('message') ?>

			<?= __('Page Expired') ?>

<?= $this->stop() ?>