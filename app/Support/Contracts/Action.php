<?php

namespace App\Support\Contracts;

/**
 * Marker interface for a single business use case (ARCHITECTURE.md §2).
 *
 * Deliberately declares no methods: PHP's interface-compatibility rules would force
 * every implementing `handle()` to share one identical parameter list, which defeats
 * the point of one Action per use case. This interface exists purely so "this class
 * is a use case" is a checkable type (`$action instanceof Action`), not a
 * hand-verified naming convention — every Action implements it and exposes its own
 * `handle(...)` method with whatever signature that use case needs.
 */
interface Action {}
