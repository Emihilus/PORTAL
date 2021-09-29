# Portal do Przeprowadzania aukcji Pieców Wędzarniczych
## Aplikacja portalu aukcyjnego stworzynego pod frameworkiem Symfony 5.3
Features:

__Aukcje:__
  - Dodawanie aukcji z opcją drag&drop obrazków dla aukcji
  - Filtrowanie aukcji po: nazwie, czy jest zakończona, po cenie, po sprzedającym, sortowanie: wg ilosci ofert, komentarzy, cenie, daty zakonczenia, utworzenia
  - System dodawania aukcji do ulubionych i wyświetlania listy ulubionych aukcji
  - Przeglądanie aukcji: galeria zdjęć aukcji stworzona za pomocą bibliotek FancyGallery i Swiper
  - Timer do zakończenia aukcji
  - Opis aukcji i nazwa aukcji pochodzą z generatora losowych imion i losowego tekstu (mojego)
  - Powiadomienie podczas gdy wygramy aukcję
  - Powiadomienie podczas gdy sprzedamy aukcję
  
  __Licytowanie:__
  - Licytować mogą tylko zalogowani
  - Powiadomienie dla użytkownika o tym, że jego oferta została przebita
  - Właściciel aukcji może ją zakończyć przed czasem,ąlbo usunąć
  
__System komentarzy:__
  - Komentotwać można aukcje i sprzedających
  - Komentarze pod aukcjami można "lajkować" i "dislajkować"
  - Można odpowiadać na komentarze
  - Edycja komentarzy
  - Komentarze nt użytkowników mogą wystawiać tylko kupujący, i określają oni czy typ komentarza to pozytywny negatywny czy neutralny
  - Sprzedający może udzielić odpowiedzi na komentarz kupującego
  - Powiadomienie podczas gdy na nasz komentarz ktoś odpowie
  - Powiadomienie podczas otrzymania komentarza sprzedaży
  - Powiadomienie podczas odpowiedzi na komentarz kupującego
    
  
  
__System powiadomień:__
  - Przy wygraniu aukcji
  - Przy sprzedaniu aukcji
  - Przy odpowiedzi na nasz komentarz
  - Przy otrzymaniu komentarza sprzedaży
  - Przy otrzymaniu odpowiedzi na komentarz sprzedaży
  - Po przeczytaniu powiadomienia są usuwane po godzinie od zobaczenia
    
 __Opcje administratora:__
  - banowanie użytkowników i ręczna weryfikacja konta
  - może usunąć albo zakonćzyć przed czasem każdą aukcję
  - może usunąć albo edytować każdy komentarz
  - panel przywracania usunietych aukcji i komentarzy
    
    
    
  W tym oczywiście system użytkowników z typowymi funkcjami typu rejestracja, zmiana hasla, aktywacja konta na mailu
  Użytkownicy mają swoje punkty za komentarze
  Na publicznej karcie użytkownia informacje takie jak liczba sprzedwanych aukcji, liczb wygranych aukcji i suma wartosci i wiele innych.
   
    
