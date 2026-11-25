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
const inboxToggle = document.querySelector('#notification-inbox-toggle');
const inboxPanel = document.querySelector('#notification-inbox-panel');
const inboxList = document.querySelector('#notification-inbox-list');
const inboxStatus = document.querySelector('#notification-inbox-status');
const inboxCount = document.querySelector('#notification-inbox-count');

const cargarNotificacionesRecientes = async () => {
    inboxStatus.textContent = 'Cargando notificaciones...';
    inboxList.replaceChildren();

    try {
        const response = await fetch(notificationElement.dataset.recentNotificationsUrl, {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error(`No se pudieron cargar las notificaciones (${response.status}).`);
        }

        const { notifications, unread_count: unreadCount } = await response.json();
        inboxCount.textContent = unreadCount > 0 ? `${unreadCount} sin leer` : 'Sin pendientes';

        if (notifications.length === 0) {
            inboxStatus.textContent = 'Todavía no hay notificaciones.';
            return;
        }

        inboxStatus.textContent = '';

        for (const notification of notifications) {
            const item = document.createElement('article');
            item.className = `notification-item px-2 py-3${notification.read ? '' : ' unread'}`;

            const heading = document.createElement('div');
            heading.className = 'd-flex justify-content-between align-items-start gap-2';

            const title = document.createElement('h3');
            title.className = 'h6 mb-1';
            title.textContent = notification.title;

            const readStatus = document.createElement('span');
            readStatus.className = 'small text-body-secondary text-nowrap';
            readStatus.textContent = notification.read ? 'Leída' : 'Sin leer';

            const message = document.createElement('p');
            message.className = 'small mb-1';
            message.textContent = notification.message;

            const createdAt = document.createElement('time');
            createdAt.className = 'small text-body-secondary';
            createdAt.dateTime = notification.created_at;
            createdAt.textContent = new Intl.DateTimeFormat('es-AR', {
                dateStyle: 'short',
                timeStyle: 'short',
            }).format(new Date(notification.created_at));

            heading.append(title, readStatus);
            item.append(heading, message, createdAt);
            inboxList.append(item);
        }
    } catch (error) {
        inboxStatus.textContent = 'No se pudieron cargar las notificaciones. Intentá nuevamente.';
        console.error('Error al cargar la bandeja de notificaciones:', error);
    }
};

if (inboxToggle && inboxPanel && notificationElement) {
    inboxToggle.addEventListener('click', () => {
        const isOpening = inboxPanel.hidden;
        inboxPanel.hidden = !isOpening;
        inboxToggle.setAttribute('aria-expanded', String(isOpening));

        if (isOpening) {
            cargarNotificacionesRecientes();
        }
    });

    document.addEventListener('click', (event) => {
        if (!inboxPanel.hidden && !inboxPanel.contains(event.target) && !inboxToggle.contains(event.target)) {
            inboxPanel.hidden = true;
            inboxToggle.setAttribute('aria-expanded', 'false');
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !inboxPanel.hidden) {
            inboxPanel.hidden = true;
            inboxToggle.setAttribute('aria-expanded', 'false');
            inboxToggle.focus();
        }
    });
}

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
                    horizontalEdge: 'bottom',
                    verticalEdge: 'right',
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
