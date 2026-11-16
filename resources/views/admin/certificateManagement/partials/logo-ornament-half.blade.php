{{--
    Logo side ornament — LEFT HALF only.
    Faithful Blade port of src/components/LogoOrnaments.tsx (LogoFlankingOrnament).
    Rendered in a <g>; the parent mirrors it with translate(280,0) scale(-1,1).
--}}
@php
    $style = $style ?? 'classic-laurel';
    $gradId = $gradId ?? 'logoOrnGrad';
    $accentColor = $accentColor ?? '#D4A32C';
    $primaryColor = $primaryColor ?? '#092653';
    $grad = 'url(#' . $gradId . ')';
@endphp

@switch($style)
    @case('khmer-kbach')
        <g>
            <path d="M 60 128 C 36 116 24 90 32 62 C 36 48 44 38 56 32"
                  fill="none" stroke="{{ $grad }}" stroke-width="2" stroke-linecap="round" />
            <path d="M 55 34 C 46 28 45 18 54 14 C 50 22 54 28 60 30 C 57 34 56 36 55 34 Z"
                  fill="{{ $grad }}" />
            <path d="M 38 52 C 23 48 18 35 28 26 C 26 36 33 42 42 45 Z"
                  fill="{{ $grad }}" />
            <path d="M 30 74 C 14 70 11 55 22 47 C 20 57 26 64 35 67 Z"
                  fill="{{ $grad }}" />
            <path d="M 31 96 C 14 96 10 80 21 71 C 20 81 26 88 36 90 Z"
                  fill="{{ $grad }}" />
            <path d="M 41 116 C 24 120 19 106 28 96 C 28 106 35 110 45 111 Z"
                  fill="{{ $grad }}" />
            <path d="M 42 62 C 50 56 54 64 47 70 M 38 84 C 48 79 51 87 44 93 M 47 104 C 57 101 59 109 52 114"
                  fill="none" stroke="{{ $grad }}" stroke-width="1.5" stroke-linecap="round" />
            <circle cx="58" cy="128" r="2.6" fill="{{ $grad }}" />
            <circle cx="24" cy="60" r="1.8" fill="{{ $grad }}" />
        </g>
        @break

    @case('rice-wheat-khmer')
        <g>
            <path d="M 56 128 C 28 106 26 68 50 38"
                  fill="none" stroke="{{ $grad }}" stroke-width="1.7" stroke-linecap="round" />
            @foreach([
                ['x' => 48, 'y' => 40, 'rot' => -55],
                ['x' => 40, 'y' => 50, 'rot' => -42],
                ['x' => 50, 'y' => 53, 'rot' => -74],
                ['x' => 35, 'y' => 63, 'rot' => -26],
                ['x' => 46, 'y' => 67, 'rot' => -62],
                ['x' => 32, 'y' => 77, 'rot' => -10],
                ['x' => 44, 'y' => 81, 'rot' => -46],
                ['x' => 33, 'y' => 92, 'rot' => 8],
                ['x' => 45, 'y' => 96, 'rot' => -30],
                ['x' => 38, 'y' => 106, 'rot' => 22],
                ['x' => 50, 'y' => 109, 'rot' => -14],
                ['x' => 47, 'y' => 119, 'rot' => 36],
            ] as $g)
                <g transform="translate({{ $g['x'] }} {{ $g['y'] }}) rotate({{ $g['rot'] }})">
                    <path d="M -7 0 C -3 -3.5 3 -3.5 7 0 C 3 3.5 -3 3.5 -7 0 Z"
                          fill="{{ $grad }}" />
                    <line x1="7" y1="0" x2="12" y2="-1" stroke="{{ $grad }}" stroke-width="0.7" />
                </g>
            @endforeach
        </g>
        @break

    @case('royal-wings')
        <g fill="{{ $grad }}">
            <path d="M 78 52 C 54 44 32 34 14 26 C 24 38 44 48 74 56 Z" />
            <path d="M 75 60 C 52 54 32 47 18 42 C 27 52 46 59 73 64 Z" />
            <path d="M 73 69 C 52 65 34 60 23 57 C 32 66 49 71 72 73 Z" />
            <path d="M 72 78 C 53 76 38 73 29 71 C 37 78 52 82 72 82 Z" />
            <path d="M 72 87 C 56 87 43 85 36 84 C 44 90 56 92 73 91 Z" />
            <path d="M 74 96 C 60 97 50 96 44 96 C 51 101 61 102 75 100 Z" />
            <path d="M 78 46 C 66 64 66 90 77 108"
                  fill="none" stroke="{{ $grad }}" stroke-width="1.6" stroke-linecap="round" />
        </g>
        @break

    @case('floral-baroque')
        <g fill="none" stroke="{{ $grad }}" stroke-linecap="round">
            <path d="M 68 42 C 44 26 24 38 30 56 C 35 69 52 63 48 50" stroke-width="2.1" />
            <path d="M 48 64 C 22 68 18 96 40 104 C 54 109 58 94 46 90" stroke-width="2" />
            <path d="M 42 106 C 30 118 42 130 58 124" stroke-width="1.8" />
            <circle cx="32" cy="48" r="3" fill="{{ $grad }}" stroke="none" />
            <circle cx="24" cy="82" r="2.6" fill="{{ $grad }}" stroke="none" />
            <path d="M 55 122 Q 62 116 66 124 Q 58 128 55 122 Z" fill="{{ $grad }}" stroke="none" />
        </g>
        @break

    @case('star-halo')
        <g>
            @foreach([-48, -32, -16, 0, 16, 32, 48] as $i => $angle)
                <line x1="140" y1="52"
                      x2="{{ 140 - cos(($angle * M_PI) / 180) * ($i % 2 === 0 ? 96 : 82) }}"
                      y2="{{ 52 - sin(($angle * M_PI) / 180) * ($i % 2 === 0 ? 52 : 42) }}"
                      stroke="{{ $grad }}" stroke-width="{{ $i % 2 === 0 ? '1.2' : '0.8' }}" stroke-opacity="0.65" />
            @endforeach
            <polygon points="46,44 48,50 54,50 49,54 51,60 46,56 41,60 43,54 38,50 44,50"
                     fill="{{ $grad }}" />
            <polygon points="34,68 35.5,72 40,72 36.5,75 38,79 34,76.5 30,79 31.5,75 28,72 32.5,72"
                     fill="{{ $grad }}" />
            <polygon points="46,90 47.5,93.5 51,93.5 48,96 49.5,99.5 46,97.5 42.5,99.5 44,96 41,93.5 44.5,93.5"
                     fill="{{ $grad }}" />
        </g>
        @break

    @case('diamond-crest')
        <g fill="none" stroke="{{ $grad }}">
            <path d="M 74 28 L 42 28 L 32 38 L 32 108 L 44 120 L 66 120" stroke-width="1.6" />
            <path d="M 70 35 L 47 35 L 39 43 L 39 103 L 49 113 L 62 113" stroke-width="0.9" stroke-opacity="0.85" />
            <polygon points="32,73 37,68 42,73 37,78" fill="{{ $grad }}" stroke="none" />
            <circle cx="68" cy="28" r="2.2" fill="{{ $primaryColor }}" stroke="{{ $grad }}" stroke-width="1" />
            <circle cx="64" cy="120" r="2" fill="{{ $grad }}" stroke="none" />
        </g>
        @break

    @case('laurel-crossed-wreath')
    @case('crown-laurel-wreath')
    @case('three-star-laurel')
        <g>
            <path d="M 148 131 C 94 128 38 106 34 62 C 32 42 40 28 52 18"
                  fill="none" stroke="{{ $grad }}" stroke-width="1.65" stroke-linecap="round" />
            @foreach([
                ['x' => 52, 'y' => 18, 'rot' => -55, 's' => 0.9],
                ['x' => 43, 'y' => 29, 'rot' => -120, 's' => 0.94],
                ['x' => 48, 'y' => 33, 'rot' => -42, 's' => 0.95],
                ['x' => 37, 'y' => 44, 'rot' => -135, 's' => 1.0],
                ['x' => 44, 'y' => 49, 'rot' => -48, 's' => 1.0],
                ['x' => 34, 'y' => 61, 'rot' => -152, 's' => 1.04],
                ['x' => 43, 'y' => 66, 'rot' => -52, 's' => 1.02],
                ['x' => 36, 'y' => 78, 'rot' => -168, 's' => 1.04],
                ['x' => 47, 'y' => 83, 'rot' => -42, 's' => 1.0],
                ['x' => 44, 'y' => 95, 'rot' => 175, 's' => 1.02],
                ['x' => 56, 'y' => 99, 'rot' => -28, 's' => 0.98],
                ['x' => 59, 'y' => 110, 'rot' => 158, 's' => 0.96],
                ['x' => 72, 'y' => 112, 'rot' => -14, 's' => 0.94],
                ['x' => 79, 'y' => 121, 'rot' => 145, 's' => 0.9],
                ['x' => 93, 'y' => 121, 'rot' => -4, 's' => 0.88],
                ['x' => 103, 'y' => 128, 'rot' => 138, 's' => 0.82],
                ['x' => 117, 'y' => 127, 'rot' => 6, 's' => 0.8],
            ] as $leaf)
                <g transform="translate({{ $leaf['x'] }} {{ $leaf['y'] }}) rotate({{ $leaf['rot'] }}) scale({{ $leaf['s'] }})">
                    <path d="M 0 0 C 4.5 -4.2 12.2 -3.8 18.5 0 C 12.2 3.8 4.5 4.2 0 0 Z"
                          fill="{{ $grad }}" stroke="#9A6914" stroke-width="0.35" />
                    <path d="M 2 0 Q 9 -1 15.5 0"
                          fill="none" stroke="#FFF7D6" stroke-width="0.55" stroke-opacity="0.65" />
                </g>
            @endforeach
        </g>
        @break

    @case('classic-laurel')
    @default
        <g>
            <path d="M 59 129 C 29 108 24 66 47 31"
                  fill="none" stroke="{{ $grad }}" stroke-width="1.5" stroke-linecap="round" />
            @foreach([
                ['x' => 47, 'y' => 31, 'rot' => -52, 's' => 0.92],
                ['x' => 41, 'y' => 41, 'rot' => -115, 's' => 0.95],
                ['x' => 44, 'y' => 45, 'rot' => -38, 's' => 0.96],
                ['x' => 36, 'y' => 55, 'rot' => -130, 's' => 1.0],
                ['x' => 41, 'y' => 59, 'rot' => -45, 's' => 1.0],
                ['x' => 33, 'y' => 70, 'rot' => -145, 's' => 1.04],
                ['x' => 39, 'y' => 75, 'rot' => -52, 's' => 1.02],
                ['x' => 32, 'y' => 86, 'rot' => -162, 's' => 1.04],
                ['x' => 40, 'y' => 90, 'rot' => -46, 's' => 1.0],
                ['x' => 35, 'y' => 101, 'rot' => -178, 's' => 1.02],
                ['x' => 44, 'y' => 105, 'rot' => -32, 's' => 0.98],
                ['x' => 43, 'y' => 115, 'rot' => 162, 's' => 0.98],
                ['x' => 52, 'y' => 118, 'rot' => -14, 's' => 0.95],
                ['x' => 51, 'y' => 126, 'rot' => 145, 's' => 0.9],
            ] as $leaf)
                <g transform="translate({{ $leaf['x'] }} {{ $leaf['y'] }}) rotate({{ $leaf['rot'] }}) scale({{ $leaf['s'] }})">
                    <path d="M 0 0 C 4.5 -4.2 12.2 -3.8 18.5 0 C 12.2 3.8 4.5 4.2 0 0 Z"
                          fill="{{ $grad }}" stroke="#9A6914" stroke-width="0.35" />
                    <path d="M 2 0 Q 9 -1 15.5 0"
                          fill="none" stroke="#FFF7D6" stroke-width="0.55" stroke-opacity="0.65" />
                </g>
            @endforeach
        </g>
        @break
@endswitch
