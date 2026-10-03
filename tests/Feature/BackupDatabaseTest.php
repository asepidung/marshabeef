<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\File;
use PDO;
use Tests\TestCase;

class BackupDatabaseTest extends TestCase
{
    use DatabaseMigrations;

    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'marsha-backup-'.uniqid();
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->dir);

        parent::tearDown();
    }

    public function test_backup_is_a_valid_copy_that_contains_the_data(): void
    {
        Product::create(['name' => 'STRIPLOIN', 'code' => '1002']);

        $this->artisan('app:backup-db', ['--dir' => $this->dir])->assertSuccessful();

        $files = File::glob($this->dir.DIRECTORY_SEPARATOR.'db-*.sqlite');
        $this->assertCount(1, $files);

        $copy = new PDO('sqlite:'.$files[0]);
        $this->assertSame('STRIPLOIN', $copy->query('select name from products')->fetchColumn());
    }

    public function test_old_backups_are_pruned_but_newest_are_kept(): void
    {
        File::ensureDirectoryExists($this->dir);
        foreach (['20200101-000000', '20200102-000000', '20200103-000000'] as $stamp) {
            File::put($this->dir.DIRECTORY_SEPARATOR."db-{$stamp}.sqlite", 'x');
        }

        $this->artisan('app:backup-db', ['--dir' => $this->dir, '--keep' => 2])->assertSuccessful();

        $names = array_map('basename', File::glob($this->dir.DIRECTORY_SEPARATOR.'db-*.sqlite'));
        sort($names);

        $this->assertCount(2, $names);
        $this->assertContains('db-20200103-000000.sqlite', $names);
        $this->assertNotContains('db-20200101-000000.sqlite', $names);
    }
}
