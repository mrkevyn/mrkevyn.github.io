<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Football Matches</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1 class="mt-5 mb-4">Football Matches</h1>
        <div class="row">
            <?php
                $api_url = "https://api.football-data.org/v4/matches"; // URL da sua API de futebol
                $api_key = "8b82c3b4451e41e18f1e7623ca155cb4"; // Chave da sua API

                // Realizar a requisição para a API
                $response = file_get_contents($api_url, false, stream_context_create([
                    'http' => [
                        'header' => "X-Auth-Token: $api_key"
                    ]
                ]));
                $data = json_decode($response, true);

                // Traduções dos status dos jogos
                $translations = array(
                    'played' => 'Jogado',
                    'scheduled' => 'Agendado',
                    'in_play' => 'Em Andamento',
                    'finished' => 'Finalizado',
                    'postponed' => 'Adiado',
                    'paused' => 'Intervalo',
                    'canceled' => 'Cancelado',
                    'timed' => 'Programado para hoje'
                    // Mais traduções podem ser adicionadas conforme necessário
                );

                $stage_translations = array(
                    'REGULAR_SEASON' => 'Temporada Regular',
                    'ROUND_1' => 'Pré-Libertadores',
                    'ROUND_2' => 'Pré-Libertadores',
                    'ROUND_3' => 'Pré-Libertadores',
                    'GROUP_STAGE' =>'Fase de Grupos'
                    // Adicione mais traduções conforme necessário
                );

                $area_translations = array(
                    'Italy' => 'Itália',
                    'South America' =>  'América do Sul',
                    'Spain' => 'Espanha',
                    'Germany' => 'Alemanha',
                    'England' => 'Inglaterra',
                    'France' => 'França',
                    'Portugal' => 'Portugal',
                    'Russia' => 'Rússia',
                    'Uruguay' => 'Uruguai',
                    'Argentina' => 'Argentina',
                    'Netherlands' => 'Holanda',
                    'Brazil' => 'Brasil',
                    'Japan' => 'Japão',
                    'Turkey' => 'Turquia',
                    'Poland' => 'Polônia',
                    'Croatia' => 'Croácia',
                    'Switzerland' => 'Suíça',
                    'Mexico' => 'México',
                    'Belgium' => 'Bélgica',
                    'Panama' => 'Panamá',
                    'Colombia' => 'Colômbia'
                    // Adicione mais traduções conforme necessário
                );

                // Verificar se a requisição foi bem-sucedida
                if ($data && isset($data['matches'])) {
                    $matches = $data['matches'];

                    // Ordenar as partidas por data e hora
                    usort($matches, function($a, $b) {
                        return strtotime($a['utcDate']) - strtotime($b['utcDate']);
                    });

                    // Loop através das partidas
                    foreach ($matches as $match) {
                        // Resultado dos tempos regulares
                        if ($match['status'] === 'SCHEDULED' || $match['status'] === 'TIMED') {
                            $full_time_result = 'A ser definido';
                        } else {
                            $full_time_result = $match['score']['fullTime']['home'] . ' - ' . $match['score']['fullTime']['away'];
                        }
                        

                        // Resultado dos pênaltis (se aplicável)
                        $penalties_result = "";
                        if (isset($match['score']['penalties'])) {
                        $penalties_result = $match['score']['penalties']['home'] . ' - ' . $match['score']['penalties']['away'];
                        }
                        // Informações da partida
                        $status = strtolower($match['status']);
                        $status_translation = isset($translations[$status]) ? $translations[$status] : ucfirst($status); // Traduz o status da partida
                        // Traduz o estágio da partida
                        $stage_translation = isset($stage_translations[$match['stage']]) ? $stage_translations[$match['stage']] : ucfirst($match['stage']);
                        $area_translation = isset($area_translations[$match['area']['name']]) ? $area_translations[$match['area']['name']] : $match['area']['name'];
                        echo '<div class="col-md-6">';
                        echo '<div class="card match-card">';
                        echo '<div class="card-body">';
                        echo '<h5 class="card-title">Partida: ' . $match['homeTeam']['name'] . ' vs ' . $match['awayTeam']['name'] . '</h5>';
                        echo '<p class="card-text team-name">' . $match['homeTeam']['name'] . ' ' . $match['score']['fullTime']['home'] . ' - ' . $match['score']['fullTime']['away'] . ' ' . $match['awayTeam']['name'] .'</p>';
                        echo '<div id="match-date-time-' . $match['id'] . '" class="card-text match-details"></div>'; // Local para exibir a data e hora usando JavaScript
                        echo '<p class="card-text match-details">Status: <span class="status status-' . $status . '">' . $status_translation . '</span></p>';
                        echo '<p class="card-text match-details">Competição: ' . $match['competition']['name'] . '</p>';
                        echo '<p class="card-text match-details">Estágio: ' . $stage_translation . '</p>';
                        echo '<p class="card-text match-details">Rodada: ' . $match['matchday'] . '</p>';
                        echo '<p class="card-text" match-details">Região: ' . $area_translation . '</p>';
                        echo '<p class="card-text match-details">Resultado dos tempos regulares: ' . $full_time_result . '</p>';
                        if (!empty($penalties_result)) {
                            echo '<p class="card-text match-details">Resultado dos pênaltis: ' . $penalties_result . '</p>';
                        }
                        // Verificar se há odds disponíveis
                        if (isset($match['odds'])) {
                            echo '<p class="card-text match-details">Odds: ' . $match['odds']['msg'] . '</p>';
                        }
                        // Verificar se há árbitros disponíveis
                        if (!empty($match['referees'])) {
                            echo '<p class="card-text match-details">Árbitros:</p>';
                            echo '<ul class="list-unstyled">';
                            foreach ($match['referees'] as $referee) {
                                echo '<li><i class="fas fa-user"></i> ' . $referee['name'] . ' (' . $referee['nationality'] . ')</li>';
                            }
                            echo '</ul>';
                        }
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="col-md-12">';
                    echo '<p>Nenhuma partida encontrada.</p>';
                    echo '</div>';
                }

                
            ?>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
    <?php foreach ($matches as $match): ?>
        // Obtém a data e hora UTC da partida
        var utcDate_<?php echo $match['id']; ?> = '<?php echo $match['utcDate']; ?>';

        // Cria um objeto Date com a data e hora UTC
        var date_<?php echo $match['id']; ?> = new Date(utcDate_<?php echo $match['id']; ?>);

        // Formata a data local do usuário (DD/MM/YYYY)
        var localDate_<?php echo $match['id']; ?> = date_<?php echo $match['id']; ?>.toLocaleDateString('pt-BR');

        // Formata a hora local do usuário (HH:MM)
        var localTime_<?php echo $match['id']; ?> = date_<?php echo $match['id']; ?>.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});

        // Adiciona "H" após os minutos
        localTime_<?php echo $match['id']; ?> += 'H';

        // Exibe a data e hora local na página
        document.getElementById('match-date-time-<?php echo $match['id']; ?>').innerHTML = '<p>Data e Hora (Local): ' + localDate_<?php echo $match['id']; ?> + ' ' + localTime_<?php echo $match['id']; ?> + '</p>';
    <?php endforeach; ?>
</script>

</body>
</html>
