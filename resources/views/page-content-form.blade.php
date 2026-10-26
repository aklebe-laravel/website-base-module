<div class="website-base-dt-forms">
    <div class="">
        @error('name'){{ $message }}@enderror
    </div>

    <div class="">
        @if (is_array($contentView))
            @foreach($contentView as $contentViewItem)
                @include($contentViewItem)
            @endforeach
        @else
            @include($contentView)
        @endif
    </div>
</div>
