@props(['columns' => 5])

<tr class="animate-pulse">
    @for ($i = 0; $i < $columns; $i++)
        <td class="whitespace-nowrap px-6 py-4">
            <div class="h-4 rounded bg-zinc-200 dark:bg-zinc-700" style="width: {{ rand(60, 100) }}%"></div>
        </td>
    @endfor
</tr>
