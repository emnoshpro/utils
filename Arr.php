<?php
//
// +----------------------------------------------------------------------+
// | Arr.php                                                              |
// +----------------------------------------------------------------------+
// | Helper methods for arrays based on Generator functions. It also allow|
// | s using old methods when an iterator is not Traversable.             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2021                                                   |
// +----------------------------------------------------------------------+
// | Authors: Mehernosh Mohta <mehernosh.mohta@gmail.com.au>              |
// +----------------------------------------------------------------------+
//
    namespace EM\Utils;

    class Arr
    {
        public static function meminfo($peak, $raw = false)
        {
            $memory = 0;
            if ($peak === true) {
                $memory = memory_get_peak_usage($raw);
            } else {
                $memory = memory_get_usage($raw);
            }

            if ($memory >= 1024 * 1024 * 1024) {
                return sprintf('%.1f GiB', $memory / 1024 / 1024 / 1024);
            }

            if ($memory >= 1024 * 1024) {
                return sprintf('%.1f MiB', $memory / 1024 / 1024);
            }

            if ($memory >= 1024) {
                return sprintf('%d KiB', $memory / 1024);
            }

            return sprintf('%d B', $memory);
        }

        public static function chunk(iterable $iterable, int $chunk_size = 1000, bool $preserve_keys = false)
        {
            $chunk_size = (int)$chunk_size;
            if (!is_iterable($iterable) || $chunk_size <= 0) {
                return false;
            }

            if ($iterable instanceof \Traversable) {
                // for iterator
                $generator = function ($iterable, $chunk_size, $preserve_keys) {
                    $count = 0;
                    $chunk = [];
                    foreach ($iterable as $key => $value) {
                        if ($preserve_keys) {
                            $chunk[$key] = $value;
                        } else {
                            $chunk[] = $value;
                        }

                        $count++;

                        if ($count === $chunk_size) {
                            yield $chunk;
                            $count = 0;
                            $chunk = [];
                        }
                    }

                    if ($count !== 0) {
                        yield $chunk;
                    }
                };

                return $generator($iterable, $chunk_size, $preserve_keys);
            }

            // preserve the old functionality when iterators are not passed
            return array_chunk($iterable, $chunk_size, $preserve_keys);
        }

        public static function map(callable $function, iterable $iterable)
        {
            if (!is_iterable($iterable)) {
                return false;
            }

            if ($iterable instanceof \Traversable) {
                $generator = function ($function, $iterable) {
                    foreach ($iterable as $key => $value) {
                        yield $key => $function($value);
                    }
                };

                return $generator($function, $iterable);
            }

            return array_map($function, $iterable);
        }

        public static function apply(callable $function, iterable $iterable)
        {
            if (!is_iterable($iterable)) {
                return false;
            }

            if ($iterable instanceof \Traversable) {
                $generator = function ($function, $iterable) {
                    foreach ($iterable as $key => $value) {
                        $function($value);
                    }
                };

                return $generator($function, $iterable);
            }

            return array_map($function, $iterable);
        }

        public static function flatten(iterable $iterable)
        {
            if (!is_iterable($iterable)) {
                return false;
            }

            if ($iterable instanceof \Traversable) {
                foreach ($iterable as $key => $value) {
                    if (is_array($value)) {
                        yield from flatten($value);
                    } else {
                        yield $key => $value;
                    }
                }
            } else {
                $return = [];
                array_walk_recursive($iterable,
                    function($a) use (&$return) {
                        $return[] = $a;
                    }
                );
                return $return;
            }
        }

        public static function duplicates(iterable $iterable, string $column_name = '', $case_sensitive = true)
        {
            // Find duplicate values for a given column name
            $duplicates = [];
            if (!is_iterable($iterable)
                || empty($column_name)
            ) {
                return $duplicates;
            }

            if ($iterable instanceof \Traversable) {
                foreach ($iterable as $row) {
                    $data = trim($row[$column_name]);
                    if ($case_sensitive === true) {
                        $data = strtolower($data);
                    }

                    if ($data === strtolower($column_name)) {
                        // we need to exclude column name if its in the header rows
                        continue;
                    }

                    if (!isset($duplicates[$data])) {
                        $duplicates[$data] = 1;
                    } else {
                        $duplicates[$data]++;
                    }
                }

                $duplicates = array_filter($duplicates, function($key, $value) {
                    return $key > 1;
                }, ARRAY_FILTER_USE_BOTH);

                return $duplicates;
            }

            return array_unique($iterable);
        }

        public static function range($start, $stop, $step)
        {
            if ($start > $stop) {
                if ($step >= 0) {
                    throw new \InvalidArgumentException('step must be negative when start > stop');
                }

                for ($i = $start; $i >= $stop; $i += $step) {
                    yield $i;
                }
            } else if ($start < $stop) {
                if ($step <= 0) {
                    throw new \InvalidArgumentException('step must be positive when start < stop');
                }

                for ($i = $start; $i <= $stop; $i += $step) {
                    yield $i;
                }
            } else {
                // both start and stop are same
                yield $start;
            }
        }

        public static function apply(iterable $iterable, callable $function)
        {
            if (!is_iterable($iterable)) {
                return false;
            }

            if ($iterable instanceof Traversable) {
                $generator = function ($iterable, $function) {
                    foreach ($iterable as $key => $value) {
                        $function($value);
                    }
                };

                return $generator($iterable, $function);
            }

            return array_map($function, $iterable);
        }

        public static function map(iterable $iterable, callable $function)
        {
            if (!is_iterable($iterable)) {
                return false;
            }

            if ($iterable instanceof Traversable) {
                $generator = function ($iterable, $function) {
                    foreach ($iterable as $key => $value) {
                        yield $key => $function($value);
                    }
                };

                return $generator($iterable, $function);
            }

            return array_map($function, $iterable);
        }
    }
