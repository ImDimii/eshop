<?php if (!defined('INCLUDED')) die('Direct access not permitted'); ?>

<?php if ($error): ?>
<div class="alert alert-error">
    <?php echo htmlspecialchars($error); ?>
</div>
<?php endif; ?>

<?php if ($ticket): ?>
<div class="ticket-info">
    <div class="ticket-status status-<?php echo $ticket['stato']; ?>">
        <?php
        $stati = [
            'aperto' => 'Aperto',
            'in_lavorazione' => 'In Lavorazione',
            'chiuso' => 'Chiuso'
        ];
        echo $stati[$ticket['stato']] ?? 'Stato Sconosciuto';
        ?>
    </div>

    <div class="ticket-details">
        <div class="ticket-message">
            <strong>La tua richiesta:</strong>
            <p><?php echo nl2br(htmlspecialchars($ticket['messaggio'])); ?></p>
        </div>

        <?php if ($ticket['risposta']): ?>
        <div class="ticket-response">
            <strong>Risposta:</strong>
            <p><?php echo nl2br(htmlspecialchars($ticket['risposta'])); ?></p>
        </div>
        <?php else: ?>
        <p class="no-response">Non è ancora presente una risposta al tuo ticket.</p>
        <?php endif; ?>

        <div class="ticket-meta">
            Ticket creato il: <?php echo date('d/m/Y H:i', strtotime($ticket['data_creazione'])); ?>
        </div>
    </div>
</div>
<?php endif; ?>

<form method="GET" action="supporto.php">
    <input type="hidden" name="view" value="ticket">
    
    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required 
               placeholder="Inserisci la tua email"
               value="<?php echo htmlspecialchars($_GET['email'] ?? ''); ?>">
    </div>

    <div class="form-group">
        <label for="codice_ordine">Codice Ordine</label>
        <input type="text" id="codice_ordine" name="codice_ordine" required 
               placeholder="Inserisci il codice del tuo ordine"
               value="<?php echo htmlspecialchars($_GET['codice_ordine'] ?? ''); ?>">
    </div>

    <button type="submit" class="btn">Verifica Stato</button>
</form>

<style>
.ticket-info {
    margin-bottom: 2rem;
}

.ticket-status {
    display: inline-block;
    padding: 0.5rem 1rem;
    border-radius: 9999px;
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 1rem;
}

.status-aperto {
    background-color: #fee2e2;
    color: #991b1b;
}

.status-in_lavorazione {
    background-color: #fef3c7;
    color: #92400e;
}

.status-chiuso {
    background-color: #dcfce7;
    color: #166534;
}

.ticket-details {
    background-color: #f8fafc;
    border-radius: 0.5rem;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.ticket-message,
.ticket-response {
    margin-bottom: 1.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid #e2e8f0;
}

.ticket-message strong,
.ticket-response strong {
    display: block;
    margin-bottom: 0.5rem;
    color: var(--text-color);
}

.ticket-message p,
.ticket-response p {
    color: var(--text-color);
    line-height: 1.6;
}

.no-response {
    color: #64748b;
    font-style: italic;
}

.ticket-meta {
    font-size: 0.875rem;
    color: #64748b;
}

button.btn {
    border: none;
    cursor: pointer;
    width: 100%;
    margin-top: 1.5rem;
}

button.btn:hover {
    background-color: var(--secondary-color);
}
</style> 