@php
    use App\Classes\Help;
    
    // Get modal single and plural names
    $model_name = Str::kebab(class_basename($model));
    $model_plural = Str::plural($model_name);
    
    // Get model fields
    $fields = $model::getTableFields();
    
    // Get pagination parameters
    $query ??= "";
    $advanced ??= [];
    $advanced_values ??= [];
    $modelfilter ??= [];
    $page ??= 1;
    $limit ??= config("searchtable.limit");
    $disableaddbutton ??= false;
    $disablesearchbar ??= false;
    $disabletotalrow ??= false;
    $showadvancefilters ??= false;
    $fit ??= false;
    
    // Get model primary key
    $model_key = $model::getModelKey();
    
    // Prepare query filters
    $filter = [];
    $filter_values = [];
    
    $search = $model::filter([
        "query" => $query,
        "advanced_search" => $advanced,
        "modelfilter" => $modelfilter,
    ], true);
    
    // Clone search for count rows
    $count = clone($search);
@endphp
{{-- <div class="row">
    <div class="col-md-12">
        @dump($search->dump())
    </div>
</div> --}}
{{-- <x-search-table-filters.$model_plural-filters /> --}}
@if(!empty($showadvancefilters))
    <div class="row justify-content-center">
        <div class="col-md-{{$size ?? 8}}">
            <div class="col-md-12 text-end">
                <button class="btn btn-primary btn-sm" onclick="$('#advanced-search').toggleClass('d-none')">
                    <i class="fa-brands fa-searchengin"></i>
                    Ricerca Avanzata
                </button>
            </div>
            <div id="advanced-search" class="row gy-2 d-none">
                @include("components.search-table-filters.$model_plural-filters")
            </div>
        </div>
    </div>
@endif
<div class="row justify-content-center mt-3">
    <div class="col-md-{{$size ?? 8}}">
        <table class="{{$model_plural}}-table table-search table table-hover table-striped {{$fit ? "table-fit" : ""}}">
            {{-- Table header --}}
            <thead class="table-dark">
                @if (!($disablesearchbar ?? false) || !($disableaddbutton ?? false))
                    <tr>
                        <th colspan="100%">
                            <div class="container-fluid px-0">
                                <div class="row">
                                    @empty($disablesearchbar)
                                        {{-- Filter input --}}
                                        <div class="col align-self-center">
                                            <input type="text" id="filter" name="filter" class="form-control p-1" value="{{$query}}" placeholder="Cerca" 
                                                hx-get="{{route($model_plural.'.index')}}" hx-trigger="keyup changed" hx-target="#{{$model_plural}}-table-data" hx-include="[name^='advanced_search']"
                                                hx-vals='{{json_encode(["modelfilter" => $modelfilter, "disableaddbutton" => $disableaddbutton, "disablesearchbar" => $disablesearchbar, "disabletotalrow" => $disabletotalrow])}}'
                                                >
                                        </div>
                                    @endempty
                                    
                                    {{-- Add new button --}}
                                    @empty($disableaddbutton)
                                        <div class="col-{{$disablesearchbar ? "12" : "auto"}} py-2 ps-0 text-end">
                                            @if(!empty($addRedirect))
                                                <a href="{{$addRedirect}}" class="text-white">
                                                    <i role="button" class="add-new fa-solid fa-plus pe-2"></i>
                                                </a>
                                            @else
                                                <i role="button" id="add-new-{{$model_name}}" class="add-new fa-solid fa-plus pe-2"
                                                    data-bs-target="#modal" data-bs-toggle="modal"
                                                    hx-get="{{route($model_plural.".create", [])}}" hx-target="#modal .modal-content"
                                                    hx-vals='{{json_encode(["modelfilter" => $modelfilter, "disableaddbutton" => $disableaddbutton, "disablesearchbar" => $disablesearchbar, "disabletotalrow" => $disabletotalrow])}}'
                                                    ></i>
                                            @endif
                                        </div>
                                    @endempty
                                </div>
                            </div>
                        </th>
                    </tr>
                @endif
                <tr>
                    {{-- Fields header --}}
                    @foreach ($fields as $key => $field)
                        @continue($field["hidden"] ?? false)
                        <th>{{$field["custom-label"] ?? Str::ucfirst(__("validation.attributes.".$key))}}</th>
                    @endforeach
                    
                    {{-- Primary column header --}}
                    @if (method_exists($model, "getTableActions") && count($model->getTableActions($model_plural, $model->{$model_key})) > 0)
                        <th class="text-end">Gestisci</th>
                    @else
                        <th></th>
                    @endif
                </tr>
            </thead>
            
            {{-- Table body --}}
            <tbody id="{{$model_plural}}-table-data">
                @fragment("search-table-body")
                    
                    {{-- <tr>
                        <td>
                            @dump($search->dumpRawSql())
                        </td>
                    </tr> --}}
                    
                    {{-- Loop trough results --}}
                    @foreach ($search->paginate($limit, ['*'], 'page', $page) as $model_obj)
                        <tr data-id="{{($model_obj->{$model_key})}}">
                            {{-- Model fields --}}
                            @foreach ($fields as $key => $field)
                                @continue($field["hidden"] ?? false)
                                <td>{!!(!empty($field["custom-value"]) ? $model_obj->{$field["custom-value"]}() : $model_obj->{$key})!!}</td>
                            @endforeach
                            
                            @if (method_exists($model, "getTableActions") && count($model_obj->getTableActions($model_plural, $model->{$model_key})) > 0)
                                {{-- Model edit button --}}
                                <td class="text-end">
                                    @foreach ($model_obj->getTableActions($model_plural, $model_obj->{$model_key}) as $action)
                                        @continue(is_null($action))
                                        @isset($action["url"])
                                            <a href="{{$action["url"]}}" class="d-inline-block ms-3 text-decoration-none text-black" {!!$action["attributes"] ?? ""!!}>
                                                {!!$action["content"]!!}
                                            </a>
                                        @else
                                            <span class="d-inline-block ms-3" title="{{$action["title"] ?? ""}}" {!!$action["attributes"]!!} role="button">
                                                {!!$action["content"]!!}
                                            </span>
                                        @endisset
                                    @endforeach
                                </td>
                            @else
                                <td></td>
                            @endif
                        </tr>
                    @endforeach
                    
                    {{-- Table totals row --}}
                    @if (!$disabletotalrow)
                        <tr class="table-dark">
                            {{-- Fields totals --}}
                            @foreach ($fields as $key => $field)
                                @continue($field["hidden"] ?? false)
                                
                                @php
                                    $sum = "";
                                    
                                    if($field["sum"] ?? false) {
                                        $sum_query = clone($count);
                                        
                                        $sum = $sum_query->sum($key);
                                        
                                        if($field["sum"] == "currency"){
                                            $sum = number_format($sum, 2, ",", ".")." €";
                                        }
                                    }
                                @endphp
                                <th class="text-{{($field["align"] ?? null) == "right" ? "end" : "start"}}">
                                    {{$sum}}
                                </th>
                            @endforeach
                            
                            {{-- Row count --}}
                            @php
                                $count_query = clone($count);
                                $total_rows = $count_query->count();
                            @endphp
                            <th class="text-end">
                                {{$total_rows}}
                            </th>
                        </tr>
                    @endif
                    
                    {{-- Pagination --}}
                    <tr class="table-dark">
                        <td class="text-center" colspan="100%">
                            <div class="row">
                                <div class="col text-end align-self-center">
                                    <button class="paginator text-white bg-transparent border-0" data-action="previous">
                                        <i class="fa-solid fa-chevron-left"></i>
                                    </button>
                                </div>
                                <div class="col-auto align-self-center">
                                    <input type="hidden" name="advanced_search[blank]" value="">
                                    <input type="text" id="page" name="page" class="table-page" value="{{$page}}"
                                        hx-get="{{route($model_plural.'.index')}}" hx-trigger="keyup changed, change" hx-target="#{{$model_plural}}-table-data" hx-include="[name='filter'],[name^='advanced_search']"
                                        hx-vals='{{json_encode(["modelfilter" => $modelfilter, "disableaddbutton" => $disableaddbutton, "disablesearchbar" => $disablesearchbar, "disabletotalrow" => $disabletotalrow])}}'
                                        >
                                    /
                                    <span id="last-page">{{ceil($count->count() / $limit)}}</span>
                                </div>
                                <div class="col text-start align-self-center">
                                    <button class="paginator text-white bg-transparent border-0" data-action="next">
                                        <i class="fa-solid fa-chevron-right"></i>
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endfragment
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener("click", function(e){
        for (var el=e.target; el && el!=this; el=el.parentNode){
            if(el.matches('.paginator')){
                let action = $(el).attr("data-action");
                let current_page = parseInt($("#page").val());
                let last_page = parseInt($("#last-page").text());
                
                if(action == "previous"){
                    current_page -= current_page > 1;
                }
                else if(action == "next"){
                    current_page += current_page < last_page;
                }
                
                $("#page").val(current_page);
                htmx.trigger("#page", "change");
                
                break;
            }
        }
    }, false);
</script>

<style>
    .table-fit{
        td, th{
            
            &:not(:last-child){
                width: 1%;
                padding-right: 20px;
                white-space: nowrap;
            }
        }
    }
</style>