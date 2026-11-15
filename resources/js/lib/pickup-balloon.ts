import type { PickupPointData } from '@/types/pickupPoint';

const DEFAULT_ICON_COLOR = '#c05a2e';

export function escapeHtml(value: string): string {
    return value
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;');
}

/**
 * Balloon content for a pickup point placemark.
 *
 * Built with inline styles because the Yandex Maps balloon is rendered
 * outside of React and outside of the document stylesheets.
 */
export function buildPickupBalloonContent(
    point: PickupPointData,
    iconColor: string = DEFAULT_ICON_COLOR,
): string {
    const name = escapeHtml(point.name);
    const address = escapeHtml(point.address);
    const phone = point.phone ? escapeHtml(point.phone) : null;
    const email = point.email ? escapeHtml(point.email) : null;

    return `
    <div style="
      padding: 16px;
      width: 300px;
      font-family: 'Segoe UI', system-ui, sans-serif;
    ">
      <div style="
        font-weight: 600;
        font-size: 15px;
        color: #2c2a26;
        margin-bottom: 8px;
        padding-bottom: 8px;
        border-bottom: 2px solid ${iconColor};
      ">
        ${name}
      </div>

      <div style="display: flex; flex-direction: column; gap: 6px;">
        <div style="font-size: 14px; color: #7d7669;">
          ${address}
        </div>

        ${
            point.description
                ? `
          <div style="font-size: 12px; color: #7d7669;">
            ${escapeHtml(point.description)}
          </div>
        `
                : ''
        }

        ${
            phone
                ? `
          <div style="font-size: 14px; color: #2c2a26;">
            <a href="tel:${phone}" style="color: #a04925; text-decoration: none;">${phone}</a>
          </div>
        `
                : ''
        }

        ${
            email
                ? `
          <div style="font-size: 14px; color: #2c2a26;">
            <a href="mailto:${email}" style="color: #a04925; text-decoration: none;">${email}</a>
          </div>
        `
                : ''
        }

        ${
            point.opening_hours
                ? `
          <div style="font-size: 12px; color: #7d7669; white-space: pre-line;">
            ${escapeHtml(point.opening_hours)}
          </div>
        `
                : ''
        }
      </div>
    </div>`;
}
