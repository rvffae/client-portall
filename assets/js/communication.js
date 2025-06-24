// Animation d'entrée des emails
        document.addEventListener('DOMContentLoaded', function() {
            const emailItems = document.querySelectorAll('.gmail-email-item');
            const chatItems = document.querySelectorAll('.gchat-conversation');
            
            emailItems.forEach((item, index) => {
                item.style.opacity = '0';
                item.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    item.style.transition = 'all 0.5s ease';
                    item.style.opacity = '1';
                    item.style.transform = 'translateY(0)';
                }, index * 100);
            });
            
            chatItems.forEach((item, index) => {
                item.style.opacity = '0';
                item.style.transform = 'translateX(-20px)';
                setTimeout(() => {
                    item.style.transition = 'all 0.5s ease';
                    item.style.opacity = '1';
                    item.style.transform = 'translateX(0)';
                }, index * 150 + 300);
            });
        });

        // Gestion du formulaire de chat
        document.querySelector('.gchat-input-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const input = document.querySelector('.gchat-input');
            const message = input.value.trim();
            
            if (message) {
                // Ici vous ajouteriez la logique pour envoyer le message
                console.log('Message envoyé:', message);
                input.value = '';
                
                // Animation du bouton
                const btn = document.querySelector('.gchat-send-btn');
                btn.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    btn.style.transform = 'scale(1)';
                }, 150);
            }
        });