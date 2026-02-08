<?php
$css = <<<CSS
<style>
.tasks {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1rem;
}

.task-card {
    background: #fff;
    border-radius: 8px;
    padding: 1rem;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.task-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: .5rem;
}

.task-title {
    font-weight: 600;
    font-size: 1.05rem;
}

.task-status {
    font-size: .75rem;
    padding: .2rem .5rem;
    border-radius: 4px;
}

.status-open { background: #eee; }
.status-done { background: #c8f7c5; }
.status-canceled { background: #f7c5c5; }

.task-meta {
    font-size: .85rem;
    color: #555;
    margin-bottom: .75rem;
    display: flex;
    gap: .75rem;
    flex-wrap: wrap;
}

.task-actions {
    display: flex;
    gap: .5rem;
}

.task-action {
    text-decoration: none;
    font-size: 1.1rem;
}
</style>
CSS;
