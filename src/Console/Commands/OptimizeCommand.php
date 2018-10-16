<?php

declare(strict_types=1);

/**
 * This file is part of Scout Extended.
 *
 * (c) Algolia Team <contact@algolia.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Algolia\ScoutExtended\Console\Commands;

use Illuminate\Console\Command;
use Algolia\ScoutExtended\Algolia;
use Algolia\ScoutExtended\Settings\Compiler;
use Algolia\ScoutExtended\Settings\LocalFactory;
use Algolia\ScoutExtended\Helpers\SearchableFinder;
use Algolia\ScoutExtended\Repositories\LocalSettingsRepository;

final class OptimizeCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $signature = 'scout:optimize {searchable? : The name of the searchable}';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Optimize the given searchable creating a settings file';

    /**
     * {@inheritdoc}
     */
    public function handle(
        Algolia $algolia,
        LocalFactory $localFactory,
        Compiler $compiler,
        SearchableFinder $searchableFinder,
        LocalSettingsRepository $localRepository
    ): void {
        foreach ($searchableFinder->fromCommand($this) as $searchable) {
            $this->output->text('🔎 Optimizing search experience in: <info>['.$searchable.']</info>');
            $index = $algolia->index($searchable);
            if (! $localRepository->exists($index) ||
                $this->confirm('Local settings already exists, do you wish to overwrite?')) {
                $settings = $localFactory->create($index, $searchable);
                $path = $localRepository->getPath($index);
                $compiler->compile($settings, $path);
                $this->output->success('Settings file created at: '.$path);
                $this->output->note('Please review the settings file and synchronize it with Algolia using: "'.
                    ARTISAN_BINARY.' scout:sync"');
            }
        }
    }
}
