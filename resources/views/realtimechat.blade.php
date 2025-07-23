
<div class=" d-none d-md-block layout">
@foreach($messages as $message)
            @if($message->type=='admin')
                <div class="w-100 d-flex justify-content-end" style="margin-bottom: 20px;">
                    <div class="">
                    <div class="">Admin</div>
                    <div style="background: grey; padding: 10px 20px; margin-bottom: 10px;">
            @else
                <div class="w-100 d-flex justify-content-start" style="margin-bottom: 20px;">
                    <div class="">
                        <div class="">{{$message->user()->get()->first()->username}}</div>
                    <div style="background: #32a89d; padding: 10px 20px; margin-bottom: 10px;">
            @endif

                        @if($message->type=='callback')
                            Нажал на {{$message->data['text']}}
                        @elseif($message->type=='admin')
                            {{$message->data['text']}}
                        @else
                            {{$message->data['text']}}
                        @endif
                    </div>
                        @if($message->type=='admin')
                            <div class="">{{$message->created_at}}</div>
                        @else
                            <div class="">{{$message->created_at}}</div>
                        @endif
            </div>
        </div>
@endforeach
</div>
