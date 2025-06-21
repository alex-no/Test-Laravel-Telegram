<?php

namespace App\Telegram;

class TelegramMessageWrapper
{
    /**
     * TelegramMessageWrapper constructor.
     */
    public function __construct(
        protected array $update
    ) {}

    /**
     * Check if the update is a message.
     * @return bool
     */
    public function isCallback(): bool
    {
        return isset($this->update['callback_query']);
    }

    /**
     * Get the chat id.
     * @return int|null
     */
    public function getChatId(): ?int
    {
        return $this->update['message']['chat']['id']
            ?? $this->update['callback_query']['message']['chat']['id']
            ?? null;
    }

    /**
     * Get the text.
     * @return string|null
     */
    public function getText(): ?string
    {
        return $this->update['message']['text']
            ?? $this->update['callback_query']['data']
            ?? null;
    }

    /**
     * Get the message array.
     *
     */
    public function getMessage(): ?array
    {
        return $this->update['message']
            ?? $this->update['callback_query']['message']
            ?? null;
    }

    /**
     * Get the original update.
     * @return array
     */
    public function getOriginalUpdate(): array
    {
        return $this->update;
    }

    /**
     * Get the callback query id.
     * @return string|null
     */
    public function getCallbackQueryId(): ?string
    {
        return $this->update['callback_query']['id'] ?? null;
    }

    /**
     * Check if the message has media.
     * @return bool
     */
    public function hasMedia(): bool
    {
        $message = $this->getMessage();
        return isset($message['document'], $message['photo'], $message['video'], $message['audio']);
    }
}
