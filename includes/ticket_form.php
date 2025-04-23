<?php if (!defined('INCLUDED')) die('Direct access not permitted'); ?>

<?php if ($message): ?>
<div class="alert alert-success">
    <?php echo htmlspecialchars($message); ?>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-error">
    <?php echo htmlspecialchars($error); ?>
</div>
<?php endif; ?>

<form method="POST" action="supporto.php">
    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required 
               placeholder="Inserisci la tua email"
               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
    </div>

    <div class="form-group">
        <label for="codice_ordine">Codice Ordine</label>
        <input type="text" id="codice_ordine" name="codice_ordine" required 
               placeholder="Inserisci il codice del tuo ordine"
               value="<?php echo htmlspecialchars($_POST['codice_ordine'] ?? ''); ?>">
    </div>

    <div class="form-group">
        <label for="messaggio">Messaggio</label>
        <textarea id="messaggio" name="messaggio" required 
                  placeholder="Descrivi il tuo problema..."
                  rows="5"><?php echo htmlspecialchars($_POST['messaggio'] ?? ''); ?></textarea>
    </div>

    <button type="submit" class="btn">Invia Richiesta</button>
</form>

<style>
.alert {
    padding: 1rem;
    border-radius: 0.5rem;
    margin-bottom: 1.5rem;
    font-size: 0.875rem;
}

.alert-success {
    background-color: #dcfce7;
    color: #166534;
    border: 1px solid #86efac;
}

.alert-error {
    background-color: #fee2e2;
    color: #991b1b;
    border: 1px solid #fca5a5;
}

textarea {
    min-height: 150px;
    resize: vertical;
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