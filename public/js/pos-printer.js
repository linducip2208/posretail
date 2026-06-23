/**
 * POS Retail - Printer Module
 * Supports: Browser Print (window.print), Web Bluetooth (thermal printer)
 */

const PosPrinter = {
    /**
     * Print receipt using browser print dialog (regular printer)
     */
    printReceipt: function (orderData, outlet, cashier) {
        // Build simple thermal receipt HTML
        const date = new Date(orderData.created_at);
        const dateStr = date.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: '2-digit' }) +
            ' ' + date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

        let itemsHtml = '';
        orderData.items.forEach(item => {
            const name = (item.product?.name || 'Item').substring(0, 16);
            const subtotal = (item.subtotal || item.quantity * item.unit_price);
            itemsHtml += `<tr>
                <td>${name}</td>
                <td class="right">${item.quantity}</td>
                <td class="right">${formatRupiah(item.unit_price)}</td>
                <td class="right">${formatRupiah(subtotal)}</td>
            </tr>`;
        });

        const totalPaid = (orderData.payments || []).reduce((s, p) => s + (p.amount || 0), 0);
        const change = totalPaid - orderData.total_amount;

        const html = `
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                @page { margin: 0; size: 80mm auto; }
                body { font-family: 'Courier New', monospace; font-size: 12px; line-height: 1.4; width: 72mm; margin: 4mm auto; }
                .center { text-align: center; }
                .right { text-align: right; }
                .bold { font-weight: bold; }
                hr { border: none; border-top: 1px dashed #000; }
                table { width: 100%; border-collapse: collapse; }
                td { padding: 1px 0; }
                .line { display: flex; justify-content: space-between; }
            </style>
        </head>
        <body>
            <div class="center bold">POS RETAIL</div>
            <div class="center" style="font-size:10px">${outlet || ''}</div>
            <hr>
            <div class="line"><span>No: ${orderData.order_number}</span><span>${dateStr}</span></div>
            <div>Kasir: ${cashier || '-'}</div>
            ${orderData.customer ? '<div>Cust: ' + orderData.customer.name + '</div>' : ''}
            <hr>
            <table>
                <tr class="bold" style="font-size:10px"><td>Item</td><td class="right">Qty</td><td class="right">Harga</td><td class="right">Subtotal</td></tr>
                ${itemsHtml}
            </table>
            <hr>
            <div class="line"><span>Subtotal</span><span>Rp ${formatRupiah(orderData.subtotal || 0)}</span></div>
            ${orderData.discount_amount > 0 ? '<div class="line"><span>Diskon</span><span>Rp ' + formatRupiah(orderData.discount_amount) + '</span></div>' : ''}
            ${orderData.tax_amount > 0 ? '<div class="line"><span>Pajak</span><span>Rp ' + formatRupiah(orderData.tax_amount) + '</span></div>' : ''}
            <div class="line bold" style="font-size:14px;margin-top:4px"><span>TOTAL</span><span>Rp ${formatRupiah(orderData.total_amount || 0)}</span></div>
            <hr>
            <div class="line"><span>Dibayar</span><span>Rp ${formatRupiah(totalPaid)}</span></div>
            ${change > 0 ? '<div class="line"><span>Kembali</span><span>Rp ' + formatRupiah(change) + '</span></div>' : ''}
            <hr>
            <div class="center" style="font-size:10px">Terima kasih!</div>
            <div class="center" style="font-size:10px">Barang yang sudah dibeli tidak dapat ditukar</div>
            <br>
            <script>window.onload = function() { window.print(); setTimeout(function() { window.close(); }, 500); }</` + `script>
        </body>
        </html>`;

        const printWindow = window.open('', '_blank', 'width=300,height=600');
        printWindow.document.write(html);
        printWindow.document.close();
    },

    /**
     * Print to Bluetooth thermal printer using Web Bluetooth API
     */
    printBluetooth: async function (orderData, outlet, cashier) {
        try {
            const device = await navigator.bluetooth.requestDevice({
                acceptAllDevices: true,
                optionalServices: ['000018f0-0000-1000-8000-00805f9b34fb'] // Standard SPP UUID
            });

            const server = await device.gatt.connect();
            const service = await server.getPrimaryService('000018f0-0000-1000-8000-00805f9b34fb');
            const characteristic = await service.getCharacteristic(
                '00002af1-0000-1000-8000-00805f9b34fb'
            );

            // Build ESC/POS bytes
            const encoder = new TextEncoder();
            const cmd = [];

            // Initialize
            cmd.push(0x1B, 0x40); // ESC @

            // Center align
            cmd.push(0x1B, 0x61, 0x01);

            // Header
            cmd.push(0x1B, 0x45, 0x01); // Bold on
            cmd.push(...Array.from(encoder.encode('POS RETAIL\n')));
            cmd.push(0x1B, 0x45, 0x00); // Bold off
            cmd.push(...Array.from(encoder.encode((outlet || '') + '\n')));

            // Left align
            cmd.push(0x1B, 0x61, 0x00);
            cmd.push(...Array.from(encoder.encode('------------------------------\n')));

            // Order info
            const date = new Date(orderData.created_at);
            const dateStr = date.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: '2-digit' }) +
                ' ' + date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

            cmd.push(...Array.from(encoder.encode(`No: ${orderData.order_number}    ${dateStr}\n`)));
            cmd.push(...Array.from(encoder.encode(`Kasir: ${cashier || '-'}\n`)));

            if (orderData.customer) {
                cmd.push(...Array.from(encoder.encode('Cust: ' + orderData.customer.name + '\n')));
            }

            cmd.push(...Array.from(encoder.encode('------------------------------\n')));

            // Items
            orderData.items.forEach(item => {
                const name = (item.product?.name || 'Item').substring(0, 16).padEnd(16);
                const qty = String(item.quantity).padStart(3);
                const price = ('Rp ' + formatRupiah(item.unit_price)).padStart(12);
                const subtotal = ('Rp ' + formatRupiah(item.subtotal || item.quantity * item.unit_price)).padStart(12);
                cmd.push(...Array.from(encoder.encode(`${name}${qty}${price}${subtotal}\n`)));
            });

            cmd.push(...Array.from(encoder.encode('------------------------------\n')));

            // Totals
            cmd.push(...Array.from(encoder.encode(`Subtotal          Rp ${String(formatRupiah(orderData.subtotal || 0)).padStart(12)}\n`)));
            if (orderData.discount_amount > 0) {
                cmd.push(...Array.from(encoder.encode(`Diskon            Rp ${String(formatRupiah(orderData.discount_amount)).padStart(12)}\n`)));
            }
            if (orderData.tax_amount > 0) {
                cmd.push(...Array.from(encoder.encode(`Pajak             Rp ${String(formatRupiah(orderData.tax_amount)).padStart(12)}\n`)));
            }

            cmd.push(0x1B, 0x45, 0x01); // Bold on
            cmd.push(...Array.from(encoder.encode(`TOTAL             Rp ${String(formatRupiah(orderData.total_amount || 0)).padStart(12)}\n`)));
            cmd.push(0x1B, 0x45, 0x00); // Bold off

            cmd.push(...Array.from(encoder.encode('------------------------------\n')));

            const totalPaid = (orderData.payments || []).reduce((s, p) => s + (p.amount || 0), 0);
            const change = totalPaid - orderData.total_amount;

            cmd.push(...Array.from(encoder.encode(`Dibayar           Rp ${String(formatRupiah(totalPaid)).padStart(12)}\n`)));
            if (change > 0) {
                cmd.push(...Array.from(encoder.encode(`Kembalian         Rp ${String(formatRupiah(change)).padStart(12)}\n`)));
            }

            cmd.push(...Array.from(encoder.encode('------------------------------\n')));

            // Footer
            cmd.push(0x1B, 0x61, 0x01); // Center
            cmd.push(...Array.from(encoder.encode('Terima kasih!\n')));
            cmd.push(...Array.from(encoder.encode('Barang yang sudah dibeli\n')));
            cmd.push(...Array.from(encoder.encode('tidak dapat ditukar\n')));

            // Feed + Cut
            cmd.push(0x1B, 0x64, 0x03); // Feed 3 lines
            cmd.push(0x1D, 0x56, 0x41, 0x10); // Full cut

            await characteristic.writeValue(new Uint8Array(cmd));
            await device.gatt.disconnect();

            return true;
        } catch (e) {
            console.error('Bluetooth print error:', e);
            return false;
        }
    }
};

function formatRupiah(amount) {
    return new Intl.NumberFormat('id-ID').format(Math.round(amount));
}

// Expose globally
window.PosPrinter = PosPrinter;
