<!-- Khmer & Latin Typography -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Alex+Brush&family=Cinzel:wght@600;700;800;900&family=Cormorant+Garamond:ital,wght@0,600;0,700;1,400;1,600&family=Great+Vibes&family=Kantumruy+Pro:wght@400;500;600;700&family=Moul&family=Playfair+Display:ital,wght@0,700;0,900;1,400&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

<!-- Tailwind CSS (CDN for standalone preview & print fidelity) -->
<script src="https://cdn.tailwindcss.com"></script>
<style>
    .font-moul { font-family: 'Moul', cursive, serif; }
    .font-kantumruy { font-family: 'Kantumruy Pro', sans-serif; }
    .font-cinzel { font-family: 'Cinzel', serif; }
    .font-cormorant { font-family: 'Cormorant Garamond', serif; }
    .font-script { font-family: 'Great Vibes', cursive; }
    .font-signature { font-family: 'Alex Brush', cursive; }
    .font-sans-clean { font-family: 'Plus Jakarta Sans', sans-serif; }

    @page {
        size: A4 landscape;
        margin: 0;
    }
    @media print {
        body {
            margin: 0;
            padding: 0 !important;
            background: transparent !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .no-print { display: none !important; }
        .certificate-container {
            box-shadow: none !important;
            margin: 0 auto !important;
            border: 8px solid #C89B3C !important;
        }
    }
</style>
