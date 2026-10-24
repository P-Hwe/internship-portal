# Servidor SMTP de mentira para testar o envio: python3 tests/smtp_falso.py 2526 /tmp/smtp.out
import socket, sys, threading, base64
porta, saida = int(sys.argv[1]), sys.argv[2]
srv = socket.socket(); srv.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1); srv.bind(('127.0.0.1', porta)); srv.listen(5)
open(saida, 'w').close()
def atende(c):
    f = c.makefile('rwb', buffering=0)
    w = lambda t: f.write((t + '\r\n').encode())
    w('220 fake ESMTP'); msg = {'auth': []}
    while True:
        l = f.readline().decode().strip()
        if not l: break
        u = l.upper()
        if u.startswith('EHLO'): w('250-fake'); w('250 AUTH LOGIN')
        elif u == 'AUTH LOGIN': w('334 VXNlcm5hbWU6'); msg['auth'].append(base64.b64decode(f.readline().strip()).decode()); w('334 UGFzc3dvcmQ6'); msg['auth'].append(base64.b64decode(f.readline().strip()).decode()); w('235 ok')
        elif u.startswith('MAIL FROM'): msg['from'] = l[10:]; w('250 ok')
        elif u.startswith('RCPT TO'): msg['to'] = l[8:]; w('250 ok')
        elif u == 'DATA':
            w('354 go'); dados = []
            while True:
                x = f.readline().decode()
                if x in ('.\r\n', '.\n'): break
                dados.append(x)
            raw = ''.join(dados); cab, _, corpo = raw.partition('\r\n\r\n')
            assunto = ''; rt = ''
            for h in cab.split('\r\n'):
                if h.lower().startswith('reply-to:'): rt = h[9:].strip()
                if h.lower().startswith('subject:'):
                    v = h[8:].strip(); assunto = base64.b64decode(v[10:-2]).decode() if v.startswith('=?UTF-8?B?') else v
            texto = base64.b64decode(''.join(corpo.split())).decode()
            open(saida, 'a').write('AUTH=%s FROM=%s TO=%s ASSUNTO=%s CORPO_OK=%s REPLYTO=%s\n' % ('/'.join(msg['auth']), msg['from'], msg['to'], assunto, 'sim' if '<html' in texto.lower() else 'nao', rt))
            w('250 queued')
        elif u == 'QUIT': w('221 bye'); break
        else: w('250 ok')
    c.close()
while True:
    c, _ = srv.accept(); threading.Thread(target=atende, args=(c,), daemon=True).start()
