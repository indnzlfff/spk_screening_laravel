<footer class="site-footer">
    <div class="footer-main">
        <div class="footer-brand-block">
            <a class="brand footer-brand" href="{{ route('home') }}">
                <span class="brand-mark brand-mark-logo" aria-hidden="true"><img src="{{ asset('assets/school/logo-sekolah.png') }}" alt=""></span>
                <span class="brand-copy"><strong>TK Pembina Hidayatul Mubtadiin</strong><small>Yayasan Pendidikan Hidayatul Mubtadiin (YAHIMU)</small></span>
            </a>
            <p>Tempat tumbuh, belajar, dan bermain dengan nilai karakter Islami, kreativitas, dan eksplorasi alam.</p>
            <div class="footer-accreditation"><span>Akreditasi A</span><span>ISO 9001:2008</span></div>
        </div>
        <div class="footer-column"><h2>Jelajahi</h2>
            <a href="{{ route('home') }}">Beranda</a><a href="{{ route('school.about') }}#profil-guru">Profil</a><a href="{{ route('activities.index') }}">Dokumentasi</a><a href="{{ route('spmb.entry') }}">SPMB</a><a href="{{ route('account.access') }}">Akun</a>
        </div>
        <div class="footer-column footer-contact"><h2>Hubungi Kami</h2>
            <a href="https://maps.google.com/?q=Jl.+Teuku+Umar+No.+35,+Sebani,+Gadingrejo,+Pasuruan" target="_blank" rel="noopener noreferrer">Jl. Teuku Umar No. 35, RT. 05 / RW. 11, Kel. Sebani, Kec. Gadingrejo, Kota Pasuruan, Jawa Timur 67139</a>
            <a href="tel:0895349199975">0895349199975</a><a href="mailto:tkpembinahidayatulmubtadiin@gmail.com">tkpembinahidayatulmubtadiin@gmail.com</a>
            <a href="https://www.instagram.com/tk_p_hidayatulmubtadiin?dlrf=ZWpjcmowaDZkMGU0" target="_blank" rel="noopener noreferrer">Instagram <span aria-hidden="true">↗</span></a>
        </div>
    </div>
    <div class="footer-bottom"><span>© {{ date('Y') }} TK Pembina Hidayatul Mubtadiin</span><span>Hasil screening adalah pemetaan awal, bukan diagnosis atau penentu penerimaan.</span></div>
</footer>
