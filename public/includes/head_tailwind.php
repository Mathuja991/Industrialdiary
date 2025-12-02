    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {extend: {colors: {
                primary: '#2c3e50', secondary: '#3498db', accent: '#e67e22', dark: '#1a1a1a', light: '#f4f7f6'
            }}}
        }
    </script>
    <style>
        .animate-fade-in { animation: fadeIn 0.5s ease-in; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
