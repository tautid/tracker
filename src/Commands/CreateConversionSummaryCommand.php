<?php

namespace TautId\Tracker\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use TautId\Tracker\Services\PixelSummaryService;

class CreateConversionSummaryCommand extends Command
{
    public $signature = 'taut-tracker:summary-create {--date= : Date to filter conversions (created_at < date). Format: Y-m-d or Y-m-d H:i:s}';

    public $description = 'Create summary command to enhance the performance of the data';

    public function handle()
    {
        $this->info('Starting conversion summary creation...');

        $dateOption = $this->option('date');
        $date = null;

        if ($dateOption) {
            try {
                $date = Carbon::parse($dateOption);
                $this->info("Filtering conversions created before: {$date->format('Y-m-d H:i:s')}");
            } catch (\Exception $e) {
                $this->error('❌ Invalid date format. Please use Y-m-d or Y-m-d H:i:s format.');
                return Command::FAILURE;
            }
        }

        try {
            app(PixelSummaryService::class)->createPixelSummaryFromUnsavedConversion($date);

            $this->info('✅ Conversion summary created successfully!');
        } catch (\Exception $e) {
            $this->error('❌ Error creating conversion summary: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
