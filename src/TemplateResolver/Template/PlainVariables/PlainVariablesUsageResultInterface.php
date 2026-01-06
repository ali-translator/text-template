<?php

namespace ALI\TextTemplate\TemplateResolver\Template\PlainVariables;

interface PlainVariablesUsageResultInterface
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;

    /**
     * @return array<string, PlainVariableUsageDto>
     */
    public function toDtoMap(): array;

    /**
     * Example:
     * {% if is_daytime %}</p> Good day, {user_name}!  {% else %} Good evening, {user_name}! {% endif %}
     * {% for item in collection %} {item.name} - {item.age} {% endfor %}
     *
     * Result:
     * ['is_daytime', 'user_name, 'collection', 'collection.name', 'collection.age']
     * @return array<string>
     */
    public function toSimplifiedVariableNames(): array;

    public function isEmpty(): bool;
}
