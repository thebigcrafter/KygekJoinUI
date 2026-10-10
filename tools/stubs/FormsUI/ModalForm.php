<?php

declare(strict_types=1);

namespace Vecnavium\FormsUI;

use Closure;
use pocketmine\form\Form;
use pocketmine\player\Player;

final class ModalForm implements Form {
	public function __construct(Closure $handler) {}

	public function jsonSerialize() : mixed { return []; }

	public function handleResponse(Player $player, $data) : void {}

	public function setTitle(string $title) : void {}

	public function setContent(string $content) : void {}

	public function setButton1(string $text) : void {}

	public function setButton2(string $text) : void {}
}
