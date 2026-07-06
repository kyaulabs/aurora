<?php

# $KYAULabs: ErrorFeofStream.php kyau@nova 2026/07/04 -0700 Exp $


declare(strict_types=1);

namespace Tests\Unit\Fixtures;

final class ErrorFeofStream
{
    public $context;

    private static string $data = '';
    private int $position = 0;

    public static function setData(string $data): void
    {
        self::$data = $data;
    }

    public function stream_open(string $path, string $mode, int $options, ?string &$opened_path): bool
    {
        $this->position = 0;
        return true;
    }

    public function stream_read(int $count): string|false
    {
        $remaining = strlen(self::$data) - $this->position;
        if ($remaining <= 0) {
            return false;
        }
        $chunk = substr(self::$data, $this->position, $count);
        $this->position += strlen($chunk);
        return $chunk;
    }

    public function stream_eof(): bool
    {
        return false;
    }

    public function stream_stat(): array|false
    {
        return ['size' => strlen(self::$data)];
    }

    public function url_stat(string $path, int $flags): array|false
    {
        return [
            'dev'     => 0,
            'ino'     => 0,
            'mode'    => 0100644,
            'nlink'   => 1,
            'uid'     => 0,
            'gid'     => 0,
            'rdev'    => 0,
            'size'    => 0,
            'atime'   => 0,
            'mtime'   => 0,
            'ctime'   => 0,
            'blksize' => -1,
            'blocks'  => -1,
        ];
    }

    public function stream_close(): void
    {
    }

    public function stream_tell(): int
    {
        return $this->position;
    }

    public function stream_seek(int $offset, int $whence): bool
    {
        return false;
    }

    public function stream_flush(): bool
    {
        return true;
    }

    public function stream_lock(int $operation): bool
    {
        return true;
    }

    public function dir_closedir(): bool
    {
        return true;
    }

    public function dir_opendir(string $path, int $options): bool
    {
        return false;
    }

    public function dir_readdir(): string|false
    {
        return false;
    }

    public function dir_rewinddir(): bool
    {
        return false;
    }
}

// vim: ft=php sts=4 sw=4 ts=4 et :

// vim: ft=php sts=4 sw=4 ts=4 et :
