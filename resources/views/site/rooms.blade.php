@extends('layouts.site')

@section('title', 'Rooms & Suites — ' . ($settings['hotel_name'] ?? 'Grand Horizon Hotel'))

@section('content')
<section class="py-5 bg-navy text-white">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('site.home') }}">Home</a></li>
                <li class="breadcrumb-item active text-white">Rooms</li>
            </ol>
        </nav>
        <h1 class="font-display fw-bold mb-2">Rooms &amp; Suites</h1>
        <p class="text-white-50 mb-0">Find the perfect room for your stay.</p>
    </div>
</section>

<section class="py-5" style="background:var(--cream)">
    <div class="container">
        <div class="row justify-content-center mb-4">
            <div class="col-lg-5">
                <form method="GET" action="{{ route('site.rooms') }}" class="d-flex gap-2">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search room type...">
                    <button class="btn btn-gold px-3" type="submit"><i class="bi bi-search"></i></button>
                </form>
            </div>
        </div>

        @if($roomTypes->isEmpty())
            <div class="text-center py-5">
                <i class="bi bi-search fs-1 text-muted d-block mb-3"></i>
                <h5>No room types found</h5>
                <p class="text-muted">Try adjusting your search.</p>
            </div>
        @else
            <div class="row g-4">
                @foreach($roomTypes as $rt)
                <div class="col-md-6 col-lg-4">
                    <div class="room-card bg-white h-100 d-flex flex-column">
                        <img src="{{ $rt->image_url ?? 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?auto=format&fit=crop&w=900&q=80' }}" alt="{{ $rt->name }}">
                        <div class="p-4 d-flex flex-column flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="font-display fw-semibold mb-0">{{ $rt->name }}</h5>
                                <span class="badge text-bg-light fw-semibold">{{ $rt->rooms_count }} {{ Str::plural('room', $rt->rooms_count) }}</span>
                            </div>
                            <p class="text-muted small flex-grow-1">{{ Str::limit($rt->description ?? 'A comfortable stay with modern amenities.', 110) }}</p>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="fs-5 fw-bold" style="color:var(--navy)">{{ $settings['currency'] ?? '$' }}{{ number_format($rt->price, 2) }}<span class="text-muted small fw-normal"> / night</span></span>
                                <a href="{{ route('site.room-type', $rt->id) }}" class="btn btn-outline-gold btn-sm">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="mt-4">
                {{ $roomTypes->links() }}
            </div>
        @endif
    </div>
</section>
@endsection
