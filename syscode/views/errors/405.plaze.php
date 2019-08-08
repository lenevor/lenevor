<?= $this->extend('errors::layout') ?>

<?= $this->section('title') ?>

		<title><?= __('Method Not Allowed') ?></title>

<?= $this->stop() ?>

<?= $this->section('code') ?>

			<h1>405</h1>

<?= $this->stop() ?>

<?= $this->section('message') ?>

			<?= __('Method Not Allowed') ?>

<?= $this->stop() ?>