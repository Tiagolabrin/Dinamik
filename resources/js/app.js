import { Notific8 } from 'notific8';
import 'notific8/dist/notific8.css';

const escapeHtml = (value) => value.replace(/[&<>"']/g, (character) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;',
}[character]));

const notificationElement = document.querySelector('[data-unread-notifications-url]');

if (notificationElement) {
    const showUnreadNotifications = async () => {
        try {
            const response = await fetch(notificationElement.dataset.unreadNotificationsUrl, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error(`No se pudieron cargar las notificaciones (${response.status}).`);
            }

            const { notifications } = await response.json();
            const displayedIds = [];

            for (const notification of notifications) {
                const toast = await Notific8.create(escapeHtml(notification.message), {
                    title: notification.title,
                    life: 8000,
                    queue: true,
                });

                await toast.open();
                displayedIds.push(notification.id);
            }

            if (displayedIds.length === 0) {
                return;
            }

            const readResponse = await fetch(notificationElement.dataset.readNotificationsUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                credentials: 'same-origin',
                body: JSON.stringify({ ids: displayedIds }),
            });

            if (!readResponse.ok) {
                throw new Error(`No se pudieron marcar las notificaciones como leídas (${readResponse.status}).`);
            }
        } catch (error) {
            console.error('Error al mostrar notificaciones:', error);
        }
    };

    showUnreadNotifications();
}
