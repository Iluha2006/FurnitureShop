<?php

namespace App\Interfaces;

/**
 * Marker interface for commands dispatched through the command bus.
 *
 * A command describes an intention to change state. It must be named with
 * a `Command` suffix and is handled by exactly one command handler.
 */
interface CommandInterface {}
