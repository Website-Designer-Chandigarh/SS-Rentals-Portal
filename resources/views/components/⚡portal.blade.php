<?php

use Livewire\Component;

new class extends Component
{
    public string $content = 'asdasdasdasd';
};
?>

<section class="portal-panel">
    <header class="portal-header">
        <div>
            <p class="portal-kicker">Portal</p>
            <h1>SS Rentals Portal</h1>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="secondary-button">Logout</button>
        </form>
    </header>

    <p class="portal-content">{{ $content }}</p>
</section>
