<?php

/**
 * Lenevor Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file license.md.
 * It is also available through the world-wide-web at this URL:
 * https://lenevor.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@Lenevor.com so we can send you a copy immediately.
 *
 * @package     Lenevor
 * @subpackage  Base
 * @link        https://lenevor.com
 * @copyright   Copyright (c) 2019 - 2025 Alexander Campo <jalexcam@gmail.com>
 * @license     https://opensource.org/licenses/BSD-3-Clause New BSD license or see https://lenevor.com/license or see /license.md
 */

namespace Syscodes\Components\Core\Console\Commands;

use FilesystemIterator;
use Syscodes\Components\Support\Arr;
use Syscodes\Components\Console\Command;
use Syscodes\Components\Filesystem\Filesystem;
use Syscodes\Components\Support\ServiceProvider;
use Syscodes\Components\Core\Events\VendorTagPublished;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Allows validate the paths publish with tags.
 */
#[AsCommand(name: 'vendor:publish')]
class VendorPublishCommand extends Command
{
    /**
     * The filesystem instance.
     *
     * @var \Syscodes\Components\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The provider to publish.
     *
     * @var string
     */
    protected $provider = null;

    /**
     * The tags to publish.
     *
     * @var array
     */
    protected $tags = [];
    
    /**
     * The console command name.
     * 
     * @var string
     */
    protected $name = 'vendor:publish';
    
    /**
     * The console command description.
     * 
     * @var string
     */
    protected $description = 'Publish any publishable assets from vendor packages';

    /**
     * Constructor. Create a new vendor publish command instance.
     *
     * @param  \Syscodes\Components\Filesystem\Filesystem  $files
     * 
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->determineWhatShouldBePublished();

        foreach ($this->tags ?: [null] as $tag) {
            $this->publishTag($tag);
        }
    }

    /**
     * Determine the provider or tag(s) to publish.
     *
     * @return void
     */
    protected function determineWhatShouldBePublished(): void
    {
        if ($this->option('all')) {
            return;
        }

        [$this->provider, $this->tags] = [
            $this->option('provider'), (array) $this->option('tag'),
        ];

        if ( ! $this->provider && ! $this->tags) {
            $this->promptForProviderOrTag();
        }
    }

    /**
     * Prompt for which provider or tag to publish.
     *
     * @return void
     */
    protected function promptForProviderOrTag(): void
    {
        $choice = $this->components->choice(
            "Which provider or tag's files would you like to publish?",
            $choices = $this->publishableChoices()
        );

        if ($choice == $choices[0] || is_null($choice)) {
            return;
        }

        $this->parseChoice($choice);
    }

    /**
     * The choices available via the prompt.
     *
     * @return array
     */
    protected function publishableChoices(): array
    {
        return array_merge(
            ['<comment>Publish files from all providers and tags listed below</comment>'],
            preg_filter('/^/', '<fg=gray>Provider:</> ', Arr::sort(ServiceProvider::publishableProviders())),
            preg_filter('/^/', '<fg=gray>Tag:</> ', Arr::sort(ServiceProvider::publishableGroups()))
        );
    }

    /**
     * Parse the answer that was given via the prompt.
     *
     * @param  string  $choice
     * 
     * @return void
     */
    protected function parseChoice($choice)
    {
        [$type, $value] = explode(': ', strip_tags($choice));

        if ($type === 'Provider') {
            $this->provider = $value;
        } elseif ($type === 'Tag') {
            $this->tags = [$value];
        }
    }

    /**
     * Publishes the assets for a tag.
     *
     * @param  string  $tag
     * 
     * @return mixed
     */
    protected function publishTag($tag)
    {
        $pathsToPublish = $this->pathsToPublish($tag);

        if ($publishing = count($pathsToPublish) > 0) {
            $this->components->info(sprintf(
                'Publishing %sassets',
                $tag ? "[$tag] " : '',
            ));
        }

        foreach ($pathsToPublish as $from => $to) {
            $this->publishItem($from, $to);
        }

        if ($publishing === false) {
            $this->components->info('No publishable resources for tag ['.$tag.'].');
        } else {
            $this->lenevor['events']->dispatch(new VendorTagPublished($tag, $pathsToPublish));

            $this->newLine();
        }
    }

    /**
     * Get all of the paths to publish.
     *
     * @param  string  $tag
     * 
     * @return array
     */
    protected function pathsToPublish($tag): array
    {
        return ServiceProvider::pathsToPublish(
            $this->provider, $tag
        );
    }

    /**
     * Publish the given item from and to the given location.
     *
     * @param  string  $from
     * @param  string  $to
     * 
     * @return void
     */
    protected function publishItem($from, $to)
    {
        if ($this->files->isFile($from)) {
            return $this->publishFile($from, $to);
        } elseif ($this->files->isDirectory($from)) {
            return $this->publishDirectory($from, $to);
        }

        $this->components->error("Can't locate path: <{$from}>");
    }

    /**
     * Publish the file to the given path.
     *
     * @param  string  $from
     * @param  string  $to
     * 
     * @return void
     */
    protected function publishFile($from, $to)
    {
        if (( ! $this->option('existing') && ( ! $this->files->exists($to) || $this->option('force')))
            || ($this->option('existing') && $this->files->exists($to))) {
            $this->createParentDirectory(dirname($to));

            $this->files->copy($from, $to);

            $this->status($from, $to, 'file');
        } else {
            if ($this->option('existing')) {
                $this->components->twoColumnDetail(sprintf(
                    'File [%s] does not exist',
                    str_replace(base_path().'/', '', $to),
                ), '<fg=yellow;options=bold>SKIPPED</>');
            } else {
                $this->components->twoColumnDetail(sprintf(
                    'File [%s] already exists',
                    str_replace(base_path().'/', '', realpath($to)),
                ), '<fg=yellow;options=bold>SKIPPED</>');
            }
        }
    }

    /**
     * Publish the directory to the given directory.
     *
     * @param  string  $from
     * @param  string  $to
     * 
     * @return void
     */
    protected function publishDirectory($from, $to)
    {
        $this->copyDirectory($from, $to);    

        $this->status($from, $to, 'directory');
    }/**
     * Copy a directory from one location to another.
     *
     * @param  string  $directory
     * @param  string  $destination
     * @param  bool  $force
     * 
     * @return bool
     */
    public function copyDirectory($directory, $destination): bool
    {
        if ( ! $this->files->isDirectory($directory)) {
            return false;
        }

        if ( ! $this->files->isDirectory($destination)) {
            $this->files->makeDirectory($destination, 0777, true);
        }

        $items = new FilesystemIterator($directory, FilesystemIterator::SKIP_DOTS);

        foreach ($items as $item) {
            $target = $destination.DIRECTORY_SEPARATOR.$item->getBasename();

            if ($item->isDir()) {
                if ( ! $this->copyDirectory($item->getPathname(), $target)) {
                    return false;
                }

                continue;
            }

            // The current item is a file.
            if ($this->files->exists($target) && ! $this->option('force')) {
                continue;
            } else if ( ! $this->files->copy($item->getPathname(), $target)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Create the directory to house the published files if needed.
     *
     * @param  string  $directory
     * 
     * @return void
     */
    protected function createParentDirectory($directory)
    {
        if ( ! $this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }
    }

    /**
     * Write a status message to the console.
     *
     * @param  string  $from
     * @param  string  $to
     * @param  string  $type
     *  
     * @return void
     */
    protected function status($from, $to, $type)
    {
        $this->components->task(sprintf(
            'Copying %s [%s] to [%s]',
            $type,
            str_replace(base_path().DIRECTORY_SEPARATOR, '', realpath($from)),
            str_replace(base_path().DIRECTORY_SEPARATOR, '', realpath($to)),
        ));
    }

    /**
     * Get the console command options.
     * 
     * @return array
     */
    protected function getOptions(): array
    {
        return [
            ['existing', null, InputOption::VALUE_NONE, 'Publish and overwrite only the files that have already been published'],
            ['force', null, InputOption::VALUE_NONE, 'Overwrite any existing files'],            
            ['all', null, InputOption::VALUE_NONE, 'Publish assets for all service providers without prompt'],
            ['provider', null, InputOption::VALUE_OPTIONAL, 'The service provider that has assets you want to publish'],
            ['tag', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'One or many tags that have assets you want to publish'],
        ];
    }
}