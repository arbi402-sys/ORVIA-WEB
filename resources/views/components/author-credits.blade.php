@props(['user' => null, 'collaborators' => collect(), 'size' => 'sm'])

@if($user)
    <span class="inline-flex flex-wrap items-center gap-1.5">
        <a href="{{ route('profile.show', $user->username ?: $user->id) }}"
           class="inline-flex items-center gap-1 font-semibold text-[#FEFEFE] hover:text-[#A332F7] transition-colors">
            <span>{{ $user->name }}</span>
            <x-verified-badge :user="$user" :size="$size" />
        </a>

        @if($collaborators && $collaborators->count() > 0)
            @foreach($collaborators as $collab)
                @if($collab->collaborator)
                    <span class="text-[#A332F7] font-bold text-xs">×</span>
                    <a href="{{ route('profile.show', $collab->collaborator->username ?: $collab->collaborator->id) }}"
                       class="inline-flex items-center gap-1 font-semibold text-[#FEFEFE] hover:text-[#A332F7] transition-colors">
                        <span>{{ $collab->collaborator->name }}</span>
                        <x-verified-badge :user="$collab->collaborator" :size="$size" />
                    </a>
                @endif
            @endforeach
        @endif
    </span>
@endif
