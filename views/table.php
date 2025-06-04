<?php
function renderTable(array $data): string {
    if (empty($data)) return '<p>No results found.</p>';

    $html = '<table><thead><tr>';
    $first = $data[0];
    if (!is_array($first)) {
        return '<p>Data not in expected format.</p>';
    }
    foreach (array_keys($first) as $col) {
        $html .= '<th>' . htmlspecialchars($col) . '</th>';
    }
    $html .= '</tr></thead><tbody>';

    foreach ($data as $row) {
        $html .= '<tr>';
        foreach ($row as $value) {
            $cell = is_array($value) ? json_encode($value, JSON_UNESCAPED_SLASHES) : $value;
            $html .= '<td>' . htmlspecialchars($cell) . '</td>';
        }
        $html .= '</tr>';
    }
    $html .= '</tbody></table>';
    return $html;
}
?>