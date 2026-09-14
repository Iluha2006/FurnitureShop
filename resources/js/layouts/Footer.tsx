

export default function Footer() {
  return (
    <footer className="footer" id="contacts">
      <div className="container footer__grid">
        <div className="footer__col">
          <h4>Каталог</h4>
          <ul>
        
          </ul>
        </div>
        <div className="footer__col">
          <h4>Покупателям</h4>
          <ul>
            <li><span>Доставка и самовывоз</span></li>
            <li><span>Оплата и рассрочка</span></li>
            <li><span>Сборка мебели</span></li>
            <li><span>Возврат и обмен</span></li>
            <li><span>Гарантия 24 месяца</span></li>
          </ul>
        </div>
        <div className="footer__col">
          <h4>Компания</h4>
          <ul>
            <li><span>О нас</span></li>
            <li><span>Наши производства</span></li>
            <li><span>Отзывы покупателей</span></li>
            <li><span>Вакансии</span></li>
            <li><span>Контакты</span></li>
          </ul>
        </div>
        <div className="footer__col">
          <h4>Контакты</h4>
          <p>+7 (495) 123-45-67</p>
          <p>Ежедневно с 9:00 до 21:00</p>
          <p>г. Москва, ул. Мебельная, 12</p>
          <p>info@mebel-dom.ru</p>
        </div>
      </div>
      <div className="footer__bottom">
        <div className="container">
          <span>© 2026 «МебельДом». Все права защищены.</span>
          <span className="footer__note">Учебный макет онлайн-каталога и интернет-магазина мебели.</span>
        </div>
      </div>
    </footer>
  )
}